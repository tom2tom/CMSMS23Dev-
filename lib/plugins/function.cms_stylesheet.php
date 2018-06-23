<?php
#...
#Copyright (C) 2004-2018 Ted Kulp <ted@cmsmadesimple.org>
#This file is a component of CMS Made Simple <http://www.cmsmadesimple.org>
#
#This program is free software; you can redistribute it and/or modify
#it under the terms of the GNU General Public License as published by
#the Free Software Foundation; either version 2 of the License, or
#(at your option) any later version.
#
#This program is distributed in the hope that it will be useful,
#BUT withOUT ANY WARRANTY; without even the implied warranty of
#MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
#GNU General Public License for more details.
#You should have received a copy of the GNU General Public License
#along with this program. If not, see <https://www.gnu.org/licenses/>.

function smarty_function_cms_stylesheet($params, &$template)
{
	#---------------------------------------------
	# Initials
	#---------------------------------------------

    $gCms = CmsApp::get_instance();
	$config = $gCms->GetConfig();

	global $CMS_LOGIN_PAGE;
	global $CMS_STYLESHEET;
	$CMS_STYLESHEET = 1;
	$name = null;
	$design_id = -1;
	$cache_dir = $config['css_path'];
	$stylesheet = '';
	$combine_stylesheets = true;
	$fnsuffix = '';
	$trimbackground = FALSE;
	$root_url = $config['css_url'];

	#---------------------------------------------
	# Trivial Exclusion
	#---------------------------------------------

	if( isset($CMS_LOGIN_PAGE) ) return;

	#---------------------------------------------
	# Read parameters
	#---------------------------------------------

    try {
        if( !empty($params['name']) ) {
            $name = trim($params['name']);
        }
        elseif( !empty($params['designid']) ) {
            $design_id = (int)$params['designid'];
        }
		else {
            $content_obj = $gCms->get_content_object();
            if( !is_object($content_obj) ) return;
            $design_id = (int) $content_obj->GetPropertyValue('design_id');
        }
        if( !$name && $design_id < 1 ) throw new \RuntimeException('Invalid parameters, or there is no design attached to the content page');

        // @todo: change this stuff to just use // instead of protocol specific URL.

        if( isset($params['nocombine']) ) $combine_stylesheets = !cms_to_bool($params['nocombine']);

        if( isset($params['stripbackground']) )	{
            $trimbackground = cms_to_bool($params['stripbackground']);
            $fnsuffix = '_e_';
        }

        #---------------------------------------------
        # Build query
        #---------------------------------------------

        $query = null;
        if( $name != '' ) {
            // stylesheet by name
            $query = new CmsLayoutStylesheetQuery(array('name'=>$params['name']) );
        } else if( $design_id > 0 ) {
            // stylesheet by design id
            $query = new \CmsLayoutStylesheetQuery( [ 'design'=>$design_id ] );
        }
        if( !$query ) throw new \RuntimeException('Problem: Could not build a stylesheet query with the provided data');

        #---------------------------------------------
        # Execute
        #---------------------------------------------

        $nrows = $query->TotalMatches();
        if( !$nrows ) throw new \RuntimeException('No stylesheets matched the criteria specified');
        $res = $query->GetMatches();

        // we have some output, and the stylesheet objects have already been loaded.

        // Combine stylesheets
        if($combine_stylesheets) {

            // Group queries & types
            $all_media = array();
            $all_timestamps = array();
            foreach( $res as $one ) {
                $mq = $one->get_media_query();
                $mt = implode(',',$one->get_media_types());
                if( !empty($mq) ) {
                    $key = md5($mq);
                    $all_media[$key][] = $one;
                    $all_timestamps[$key][] = $one->get_modified();
                } else if( !$mt ) {
                    $all_media['all'][] = $one;
                    $all_timestamps['all'][] = $one->get_modified();
                } else {
                    $key = md5($mt);
                    $all_media[$key][] = $one;
                    $all_timestamps[$key][] = $one->get_modified();
                }

            }

            // media parameter...
            if( isset($params['media']) && strtolower($params['media']) != 'all' ) {
                // media parameter is deprecated.

                // combine all matches into one stylesheet
                $filename = 'stylesheet_combined_'.md5($design_id.serialize($params).serialize($all_timestamps).$fnsuffix).'.css';
                $fn = cms_join_path($cache_dir,$filename);

                if( !file_exists($fn) ) {
                    $list = array();
                    foreach ($res as $one) {
                        if( in_array($params['media'],$one->get_media_types()) ) $list[] = $one->get_name();
                    }

                    cms_stylesheet_writeCache($fn, $list, $trimbackground, $template);
                }

                cms_stylesheet_toString($filename, $params['media'], '', $root_url, $stylesheet, $params);

            } else {

                foreach($all_media as $hash=>$onemedia) {

                    // combine all matches into one stylesheet.
                    $filename = 'stylesheet_combined_'.md5($design_id.serialize($params).serialize($all_timestamps[$hash]).$fnsuffix).'.css';
                    $fn = cms_join_path($cache_dir,$filename);

                    // Get media_type and media_query
                    $media_query = $onemedia[0]->get_media_query();
                    $media_type = implode(',',$onemedia[0]->get_media_types());

                    if( !is_file($fn) ) {
                        $list = array();

                        foreach( $onemedia as $one ) {
                            $list[] = $one->get_name();
                        }

                        cms_stylesheet_writeCache($fn, $list, $trimbackground, $template);
                    }

                    cms_stylesheet_toString($filename, $media_query, $media_type, $root_url, $stylesheet, $params);
                }
            }

            // Do not combine stylesheets
        } else {
            foreach ($res as $one) {

                if (isset($params['media'])) {
                    if( !in_array($params['media'],$one->get_media_types()) ) continue;
                    $media_query = '';
                    $media_type = $params['media'];
                } else {
                    $media_query = $one->get_media_query();
                    $media_type  = implode(',',$one->get_media_types());
                }

                $filename = 'stylesheet_'.md5('single'.$one->get_id().$one->get_modified().$fnsuffix).'.css';
                $fn = cms_join_path($cache_dir,$filename);

                if (!file_exists($fn) ) cms_stylesheet_writeCache($fn, $one->get_name(), $trimbackground, $template);

                cms_stylesheet_toString($filename, $media_query, $media_type, $root_url, $stylesheet, $params);
            }
        }

        #---------------------------------------------
        # Cleanup & output
        #---------------------------------------------

        if( strlen($stylesheet) ) {
            $stylesheet = preg_replace("/\{\/?php\}/", "", $stylesheet);

            // Remove last comma at the end when $params['nolinks'] is set
            if( isset($params['nolinks']) && cms_to_bool($params['nolinks']) && endswith($stylesheet,',') ) {
                $stylesheet = substr($stylesheet,0,strlen($stylesheet)-1);
            }
        }
    } catch( \Exception $e ) {
        cms_error('cms_stylesheet',$e->GetMessage());
        $stylesheet = '<!-- cms_stylesheet error: '.$e->GetMessage().' -->';
    }

	// Notify core that we are no longer at stylesheet, pretty ugly way to do this. -Stikki-
	$CMS_STYLESHEET = 0;
	unset($CMS_STYLESHEET);
	unset($GLOBALS['CMS_STYLESHEET']);

	if( isset($params['assign']) ){
	    $template->assign(trim($params['assign']), $stylesheet);
	    return;
    }

	return $stylesheet;

} // end of main

/**********************************************************
	Misc functions
**********************************************************/

function cms_stylesheet_writeCache($filename, $list, $trimbackground, &$template)
{
	$_contents = '';
    if( is_string($list) && !is_array($list) ) $list = array($list);

	// Smarty processing
	$template->smarty->left_delimiter = '[[';
	$template->smarty->right_delimiter = ']]';

	try {
        foreach( $list as $name ) {
            // force the stylesheet to compile because of smarty bug:  https://github.com/smarty-php/smarty/issues/72
            $tmp = $template->smarty->force_compile;
            $template->smarty->force_compile = 1;
            $_contents .= $template->fetch('cms_stylesheet:'.$name);
            $template->smarty->force_compile = $tmp;
        }
	}
	catch (SmartyException $e) {
        // why not just re-throw the exception as it may have a smarty error in it.
        cms_error('cms_stylesheet: Smarty Compile process failed, an error in the template?');
        return;
	}

	$template->smarty->left_delimiter = '{';
	$template->smarty->right_delimiter = '}';

	// Fix background
	if($trimbackground) {

		$_contents = preg_replace('/(\w*?background-image.*?\:\w*?).*?(;.*?)/', '', $_contents);
		$_contents = preg_replace('/\w*?(background[-image]*[\s\w]*\:[\#\s\w]*)url\(.*\)/','$1;',$_contents);
		$_contents = preg_replace('/\w*?(background[-image]*[\s\w]*\:[\s]*\;)/','',$_contents);
		$_contents = preg_replace('/(\w*?background-color.*?\:\w*?).*?(;.*?)/', '\\1transparent\\2', $_contents);
		$_contents = preg_replace('/(\w*?background-image.*?\:\w*?).*?(;.*?)/', '', $_contents);
		$_contents = preg_replace('/(\w*?background.*?\:\w*?).*?(;.*?)/', '', $_contents);
	}

    \CMSMS\HookManager::do_hook('Core::StylesheetPostRender', [ 'content' => &$_contents ] );

	// Write file
	$fh = fopen($filename,'w');
	fwrite($fh, $_contents);
	fclose($fh);

} // end of writeCache

function cms_stylesheet_toString($filename, $media_query = '', $media_type = '', $root_url, &$stylesheet, &$params)
{
	if( !endswith($root_url,'/') ) $root_url .= '/';
	if( isset($params['nolinks']) )	{
		$stylesheet .= $root_url.$filename.',';
	} else {

		if (!empty($media_query)) {
			$stylesheet .= '<link rel="stylesheet" type="text/css" href="'.$root_url.$filename.'" media="'.$media_query.'" />'."\n";
		} elseif (!empty($media_type)) {

			$stylesheet .= '<link rel="stylesheet" type="text/css" href="'.$root_url.$filename.'" media="'.$media_type.'" />'."\n";
		} else {

			$stylesheet .= '<link rel="stylesheet" type="text/css" href="'.$root_url.$filename.'" />'."\n";
		}
	}

} // end of toString

/**********************************************************
	Help functions
**********************************************************/

function smarty_cms_about_function_cms_stylesheet()
{
	echo <<<'EOS'
<p>Author: jeff&lt;jeff@ajprogramming.com&gt;</p>
<p>Change History:</p>
<ul>
 <li>Rework from {stylesheet}</li>
 <li>(Stikki and Calguy1000) Code cleanup, Added grouping by media type / media query, Fixed cache issues</li>
</ul>
EOS;
}
