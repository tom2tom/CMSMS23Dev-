<?php
#Class for consolidating specified javascript's into a single file
#Copyright (C) 2018-2019 CMS Made Simple Foundation <foundation@cmsmadesimple.org>
#Thanks to Robert Campbell and all other contributors from the CMSMS Development Team.
#This file is a component of CMS Made Simple <http://www.cmsmadesimple.org>
#
#This program is free software; you can redistribute it and/or modify
#it under the terms of the GNU General Public License as published by
#the Free Software Foundation; either version 2 of the License, or
#(at your option) any later version.
#
#This program is distributed in the hope that it will be useful,
#but WITHOUT ANY WARRANTY; without even the implied warranty of
#MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
#GNU General Public License for more details.
#You should have received a copy of the GNU General Public License
#along with this program. If not, see <https://www.gnu.org/licenses/>.

namespace CMSMS;

use const CMS_SCRIPTS_PATH;
use const TMP_CACHE_LOCATION;
use function cms_get_script;
use function cms_path_to_url;
use function file_put_contents;

//TODO a job to clear old consolidations ? how old ? c.f. TMP_CACHE_LOCATION cleaner

/**
 * A class for consolidating specified javascript files and/or strings into a single file.
 *
 * @since 2.3
 * @package CMS
 */
class ScriptManager
{
    private $_items = [];
    private $_item_priority = 2;

    /**
     * Get default priority for items to be merged
     *
     * @return int 1..3 The current default priority
     */
    public function get_script_priority() : int
    {
        return $this->_item_priority;
    }

    /**
     * Set default priority for items to be merged
     *
     * @param int $val The new default priority value (constrained to 1..3)
     */
    public function set_script_priority( int $val )
    {
        $this->_item_priority = max(1,min(3,$val));
    }

    /**
     * Revert to initial state
     */
    public function reset()
    {
        $this->_items = [];
        $this->_item_priority = 2;
    }

    /**
     * Record a string to be merged
     *
     * @param string $output   js string
     * @param int    $priority Optional priority 1..3 for the script. Default 0 (use current default)
     * @param bool   $force    Optional flag whether to force recreation of the merged file. Default false
     */
    public function queue_string( string $output, int $priority = 0, bool $force = false )
    {
        $sig = md5( __FILE__.$output );
        $output_file = TMP_CACHE_LOCATION.DIRECTORY_SEPARATOR."cms_$sig.js";
        if( $force || !is_file($output_file) ) {
            file_put_contents( $output_file, $output, LOCK_EX );
        }
        $this->queue_file($output_file, $priority);
    }

    /**
     * Record a file to be merged if necessary
     *
     * @param string $filename Filesystem path of script file
     * @param int    $priority Optional priority 1..3 for the file. Default 0 (use current default)
     * @return bool indicating success
     */
    public function queue_file( string $filename, int $priority = 0 )
    {
        if( !is_file($filename) ) return false;

        $sig = md5( $filename );
        if( isset( $this->_items[$sig]) ) return false;

        if( $priority < 1 ) {
            $priority = $this->_item_priority;
        } elseif( $priority > 3 ) {
            $priority = 3;
        } else {
            $priority = (int)$priority;
        }

        $this->_items[$sig] = [
            'file' => $filename,
            'mtime' => filemtime( $filename ),
            'priority' => $priority,
            'index' => count( $this->_items )
        ];
        return true;
    }

    /**
     * Find and record a script-file to be merged if necessary
     *
     * @param string $filename absolute or relative filepath or (base)name of the
	 *  wanted script file, optionally including [.-]min before the .js extension
     *  If the name includes a version, that will be taken into account.
     *  Otherwise, any found version will be used. Min-format preferred over non-min.
     * @param int    $priority Optional priority 1..3 for the script. Default 0 (use current default)
     * @return bool indicating success
     */
    public function queue_matchedfile( string $filename, int $priority = 0 ) : bool
    {
        $cache_filename = cms_get_script($filename, false);
        if( $cache_filename ) {
            return $this->queue_file( $cache_filename, $priority );
        }
        return false;
    }

    /**
     * Construct a merged file from previously-queued files, if such file
     * doesn't exist or is out-of-date.
     * Hooks 'Core::PreProcessScripts' and 'Core::PostProcessScripts' are
     * run respectively before and after the content merge.
     *
     * @param string $output_path Optional Filesystem absolute path of folder to hold the merged file. Default '' (use TMP_CACHE_LOCATION)
     * @param bool   $force       Optional flag whether to force recreation of the merged file. Default false
     * @param bool   $allow_defer Optional flag whether to force-include jquery.cmsms_defer.js. Default true
     * @return mixed string basename of the merged-items file | null upon error
     */
    public function render_scripts( string $output_path = '', bool $force = false, bool $allow_defer = true )
    {
        if( $this->_items && !count($this->_items) ) return; // nothing to do
        $base_path = ($output_path) ? rtrim($output_path, ' /\\') : TMP_CACHE_LOCATION;
        if( !is_dir( $base_path ) ) return; // nowhere to put it

        // auto append the defer script
        if( $allow_defer ) {
            $defer_script = CMS_SCRIPTS_PATH.DIRECTORY_SEPARATOR.'jquery.cmsms_defer.js';
            $this->queue_file( $defer_script, 3 );
        }

        $tmp = Events::SendEvent( 'Core', 'PreProcessScripts', $this->_items );
        $items = ( $tmp ) ? $tmp : $this->_items;

        if( $items ) {
            if( count($items) > 1) {
                // sort the items by priority, then index (to preserve order)
                uasort( $items, function( $a, $b ) {
                    if( $a['priority'] != $b['priority'] ) return $a['priority'] <=> $b['priority'];
                    return $a['index'] <=> $b['index'];
                });
            }

            $t_sig = '';
            $t_mtime = -1;
            foreach( $items as $sig => $rec ) {
                $t_sig .= $sig;
                $t_mtime = max( $rec['mtime'], $t_mtime );
            }
            $sig = md5( __FILE__.$t_sig.$t_mtime );
            $cache_filename = "cms_$sig.js";
            $output_file = $base_path.DIRECTORY_SEPARATOR.$cache_filename;

            if( $force || !is_file($output_file) || filemtime($output_file) < $t_mtime ) {
                $output = '';
                foreach( $items as $sig => $rec ) {
                    $content = @file_get_contents( $rec['file'] );
                    if( $content ) $output .= $content."\n\n";
                }

                $tmp = Events::SendEvent( 'Core', 'PostProcessScripts', $output );
                if( $tmp ) $output = $tmp;
                file_put_contents( $output_file, $output, LOCK_EX );
            }
            return $cache_filename;
        }
    }

    /**
     * Construct a merged file from previously-queued files, if such file
     * doesn't exist or is out-of-date.
     * Then generate the corresponding html for direct use.
     * @see also ScriptManager::render_scripts()
     *
     * @param string $output_path Optional Filesystem absolute path of folder to hold the script file. Default '' (use TMP_CACHE_LOCATION)
     * @param bool   $force       Optional flag whether to force recreation of the merged file. Default false
     * @param bool   $allow_defer Optional flag whether to force-include jquery.cmsms_defer.js. Default true
     * @return string html string <script ... </script> | ''
     */
    public function render_inclusion(string $output_path = '', bool $force = false, bool $allow_defer = true ) : string
    {
        $base_path = ($output_path) ? rtrim($output_path, ' /\\') : TMP_CACHE_LOCATION;
        $cache_filename = $this->render_scripts($base_path, $force, $allow_defer);
        if( $cache_filename ) {
            $output_file = $base_path.DIRECTORY_SEPARATOR.$cache_filename;
            $url = cms_path_to_url($output_file);
            return "<script type=\"text/javascript\" src=\"$url\"></script>\n";
        }
        return '';
    }
} // class
