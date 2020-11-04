<?php
#autoloader for CMS Made Simple <http://www.cmsmadesimple.org>
#Copyright (C) 2004-2020 CMS Made Simple Foundation <foundation@cmsmadesimple.org>
#
#This file is free software; you can redistribute it and/or modify
#it under the terms of the GNU General Public License as published by
#the Free Software Foundation; either version 2 of the License, or
#(at your option) any later version.
#
#This file is distributed in the hope that it will be useful,
#but WITHOUT ANY WARRANTY; without even the implied warranty of
#MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
#GNU General Public License for more details.
#
#You should have received a copy of that license along with this file.
#If not, see <https://www.gnu.org/licenses/>.

/**
 * A function for auto-loading module-namespaced classes.
 *
 * @internal
 * @param string A possibly-namespaced class name Any leading '\' is ignored.
 */
function cmsms_spacedloader($classname)
{
    $p = strpos($classname, '\\', 1);
    if ($p !== false) {
        if ($classname[0] === '\\') {
            $classname = substr($classname, 1);
            --$p;
        }
        $findpath = function($modname) {
            if (!defined('CMS_ROOT_PATH')) {
                $config = cmsms()->GetConfig();
                define('CMS_ROOT_PATH', $config['root_path']);
            }
            $p = DIRECTORY_SEPARATOR.'modules'.DIRECTORY_SEPARATOR.$modname.DIRECTORY_SEPARATOR.$modname.'.module.php';
            $path = CMS_ROOT_PATH.$p;
            if (is_file($path)) {
                return $path;
            }

            if (defined('CMS_ASSETS_PATH')) {
                // CMSMS 2.3+ core-modules place
                $path = CMS_ROOT_PATH.DIRECTORY_SEPARATOR.'lib'.$p;
                if (is_file($path)) {
                    return $path;
                }
                // CMSMS 2.3+ other-modules place
                $path = CMS_ASSETS_PATH.$p;
                if (is_file($path)) {
                    return $path;
                }
            }
            return '';
        };
        $space = substr($classname, 0, $p);
        $mpath = $findpath($space);
        if (!$mpath) {
            return;
        }
        $root = dirname($mpath).DIRECTORY_SEPARATOR.'lib'.DIRECTORY_SEPARATOR;

        $path = strtr(substr($classname, $p + 1), '\\', DIRECTORY_SEPARATOR);
        $base = basename($path);
        $path = dirname($path);
        if ($path != '.') {
            $root .= $path.DIRECTORY_SEPARATOR;
        }
        foreach (['class.', 'trait.', 'interface.', ''] as $test) {
            $fp = $root.$test.$base.'.php';
            if (is_file($fp)) {
                if (!class_exists($space, false)) {
                    //deprecated since 2.3 - some modules require existence of this, or assume, and actually use it
                    $gCms = cmsms();
                    require_once $mpath;
                }
                require_once $fp;
                if (class_exists($classname, false)) {
                    return;
                }
            }
        }

        if (endswith($base, 'Task')) {
            $class = substr($base, 0, -4);
            foreach (['class.', ''] as $test) {
                $fp = $root.$test.$class.'.task.php';
                if (is_file($fp)) {
                    require_once $fp;
                    if (class_exists($classname, false)) {
                        return;
                    }
                } else {
                    $fp = $root.$test.$base.'.php';
                    if (is_file($fp)) {
                        require_once $fp;
                        if (class_exists($classname, false)) {
                            return;
                        }
                    }
                }
            }
        }
    }
}

spl_autoload_register('cmsms_spacedloader');