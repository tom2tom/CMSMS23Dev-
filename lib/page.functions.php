<?php
#System operation functions.
#Copyright (C) 2004-2019 CMS Made Simple Foundation <foundation@cmsmadesimple.org>
#Thanks to Ted Kulp and all other contributors from the CMSMS Development Team.
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

use CMSMS\ContentOperations;
use CMSMS\FormUtils;
use CMSMS\internal\LoginOperations;
use CMSMS\SyntaxEditor;
use CMSMS\UserOperations;

/**
 * Functions related to the underlying mechanisms of the CMSMS system.
 *
 * @package CMS
 * @license GPL
 */

/**
 * Gets the username the current cli-user
 * NOT cached/static (to support concurrent-use)
 * @since 2.3
 * @return mixed string|null
 */
function get_cliuser()
{
    $uname = exec('whoami');
    if( !$uname ) {
        $file  = tempnam(sys_get_temp_dir(), 'WHOMADE_');
        file_put_contents($file , 'test');
        $uid = fileowner($file ); //maybe false
        unlink($file );
        if( $uid ) {
            if( function_exists('posix_getpwuid') ) {
                $uname = posix_getpwuid($uid)['name']; //approximate, hack
            }
            else {
                $uname = getenv('USERNAME');
            }
        }
    }
    return $uname;
}


/**
 * Gets the userid of the current user (logged-in or otherwise).
 *
 * If an effective uid has been set in the session, AND the primary user is
 * a member of the admin group, then allow emulating that effective uid.
 *
 * @since 0.1
 * @param  boolean $redirect Optional flag, default true. Whether to redirect to
 *  the admin login page if the user is not logged in (and operating in 'normal' mode).
 * @return integer The UID of the logged in administrator, or NULL
 */
function get_userid(bool $redirect = true)
{
    $config = cms_config::get_instance();
    if( empty($config['app_mode']) ) {
        if( cmsms()->is_cli() ) {
            $uname = get_cliuser();
            if( $uname ) {
                $user = UserOperations::get_instance()->LoadUserByUsername($uname);
                if( $user ) {
                    return $user->id;
                }
            }
            return false;
        }
        //  TODO alias etc during other 'remote admin'
        $uid = LoginOperations::get_instance()->get_effective_uid();
        if( !$uid && $redirect ) {
            redirect($config['admin_url'].'/login.php');
        }
        return $uid;
    }
    return 1; //CHECKME super-admin sensible for app mode ?
}


/**
 * Gets the username of the current user (logged-in or otherwise).
 *
 * If an effective username has been set in the session, AND the primary user is
 * a member of the admin group, then return the effective username.
 *
 * @since 2.0
 * @param  boolean $redirect Optional flag, default true. Whether to redirect to
 *  the admin login page if the user is not logged in.
 * @return string the username of the user, or '', or no return at all.
 */
function get_username(bool $redirect = true)
{
    $config = cms_config::get_instance();
    if( empty($config['app_mode']) ) {
        if( cmsms()->is_cli() ) {
            return get_cliuser();
        }
        //TODO alias etc during 'remote admin'
        $uname = LoginOperations::get_instance()->get_effective_username();
        if( !$uname && $redirect ) {
            redirect($config['admin_url'].'/login.php');
        }
        return $uname;
    }
    return ''; //no username in app mode
}


/**
 * Checks to see if the user is logged in and the request has the proper key.
 * If not, normally redirects the browser to the admin login.
 *
 * Note: this method should only be called from admin operations.
 *
 * @since 0.1
 * @param boolean $no_redirect Optional flag, default false. If true, then do NOT redirect if not logged in
 * @return boolean or no return at all
 */
function check_login(bool $no_redirect = false)
{
    $redirect = !$no_redirect;
    $uid = get_userid($redirect);
    if( $uid > 0 ) {
        if( LoginOperations::get_instance()->validate_requestkey() ) {
			return true;
		}
		// still here if logged in, but no/invalid secure-key in the request
    }
	if( $redirect ) {
		// redirect to the admin login page
		// use SCRIPT_FILENAME and make sure it validates with the root_path
		if( startswith($_SERVER['SCRIPT_FILENAME'],CMS_ROOT_PATH) ) {
			$_SESSION['login_redirect_to'] = $_SERVER['REQUEST_URI'];
		}
		LoginOperations::get_instance()->deauthenticate();
		$config = cms_config::get_instance();
		redirect($config['admin_url'].'/login.php');
	}
	return false;
}


/**
 * Checks to see that the given userid has access to the given permission.
 * Members of the admin group have all permissions.
 *
 * @since 0.1
 * @param int $userid The user id
 * @param string $permname The permission name
 * @return boolean
 */
function check_permission(int $userid, string $permname)
{
    return UserOperations::get_instance()->CheckPermission($userid,$permname);
}


/**
 * Checks that the given userid has access to modify the given
 * pageid.  This would mean that they were set as additional
 * authors/editors by the owner.
 *
 * @internal
 * @since 0.2
 * @param  integer The admin user id
 * @param  integer A valid content id.
 * @return boolean
 */
function check_authorship(int $userid, int $contentid = null)
{
    return ContentOperations::get_instance()->CheckPageAuthorship($userid,$contentid);
}


/**
 * Prepares an array with the list of the pages $userid is an author of
 *
 * @internal
 * @since 0.11
 * @param  integer The user id.
 * @return array   An array of pages this user is an author of.
 */
function author_pages(int $userid)
{
    return ContentOperations::get_instance()->GetPageAccessForUser($userid);
}


/**
 * Gets the given site preference
 * @since 0.6
 *
 * @deprecated since 1.10
 * @see cms_siteprefs::get
 *
 * @param string $prefname The preference name
 * @param mixed  $defaultvalue The default value if the preference does not exist
 * @return mixed
 */
function get_site_preference(string $prefname, $defaultvalue = null)
{
    return cms_siteprefs::get($prefname,$defaultvalue);
}


/**
 * Removes the given site preference.
 * @since 0.6
 *
 * @deprecated since 1.10
 * @see cms_siteprefs::remove
 *
 * @param string $prefname Preference name to remove
 * @param boolean $uselike  Optional flag, default false. Whether to remove all preferences that are LIKE the supplied name.
 */
function remove_site_preference(string $prefname, bool $uselike = false)
{
    return cms_siteprefs::remove($prefname, $uselike);
}


/**
 * Sets the given site preference with the given value.
 * @since 0.6
 *
 * @deprecated since 1.10
 * @see cms_siteprefs::set
 *
 * @param string $prefname The preference name
 * @param mixed  $value The preference value (will be stored as a string)
 */
function set_site_preference(string $prefname, $value)
{
    return cms_siteprefs::set($prefname, $value);
}


/**
 * A method to create a text area control
 *
 * @internal
 * @access private
 * @deprecated since 2.3 instead use CMSMS\FormUtils::create_textarea()
 * @param boolean $enablewysiwyg Whether or not we are enabling a wysiwyg.  If false, and forcewysiwyg is not empty then a syntax area is used.
 * @param string  $value The contents of the text area
 * @param string  $name The name of the text area
 * @param string  $class An optional class name
 * @param string  $id An optional ID (HTML ID) value
 * @param string  $encoding The optional encoding
 * @param string  $stylesheet Optional style information
 * @param integer $width Width (the number of columns) (CSS can and will override this)
 * @param integer $height Height (the number of rows) (CSS can and will override this)
 * @param string  $forcewysiwyg Optional name of the syntax hilighter or wysiwyg to use.  If empty, preferences indicate which a syntax editor or wysiwyg should be used.
 * @param string  $wantedsyntax Optional name of the language used.  If non empty it indicates that a syntax highlihter will be used.
 * @param string  $addtext Optional additional text to include in the textarea tag
 * @return string
 */
function create_textarea(
    bool $enablewysiwyg,
    string $value,
    string $name,
    string $class = '',
    string $id = '',
    string $encoding = '',
    string $stylesheet = '',
    int $width = 80,
    int $height = 15,
    string $forcewysiwyg = '',
    string $wantedsyntax = '',
    string $addtext = ''
) {
    $parms = func_get_args() + [
        'height' => 15,
        'width' => 80,
    ];
    return FormUtils::create_textarea($parms);
}

/**
 * A convenience function to test if the site is marked as down according to the config panel.
 * This method includes handling the preference that indicates that site-down behavior should
 * be disabled for certain IP address ranges.
 *
 * @return boolean
 */
function is_sitedown() : bool
{
    global $CMS_INSTALL_PAGE;
    if( isset($CMS_INSTALL_PAGE) ) return true;

    if( cms_siteprefs::get('enablesitedownmessage') !== '1' ) return false;

    $uid = get_userid(false);
    if( $uid && cms_siteprefs::get('sitedownexcludeadmins') ) return false;

    if( !isset($_SERVER['REMOTE_ADDR']) ) return true;
    $excludes = cms_siteprefs::get('sitedownexcludes');
    if( !$excludes ) return true;
    return !cms_ipmatches($_SERVER['REMOTE_ADDR'],$excludes);
}

/**
 * Create a dropdown form element containing a list of files that match certain conditions
 *
 * @internal
 * @param string The name for the select element.
 * @param string The directory name to search for files.
 * @param string The name of the file that should be selected
 * @param string A comma separated list of extensions that should be displayed in the list
 * @param string An optional string with which to prefix each value in the output by
 * @param boolean Whether 'none' should be an allowed option
 * @param string Text containing additional parameters for the dropdown element
 * @param string A prefix to use when filtering files
 * @param boolean A flag indicating whether the files matching the extension and the prefix should be included or excluded from the result set
 * @param boolean A flag indicating whether the output should be sorted.
 * @return string maybe empty
 */
function create_file_dropdown(string $name,string $dir,string $value,string $allowed_extensions,string $optprefix='',
                              bool $allownone=false,string $extratext='',
                              string $fileprefix='',bool $excludefiles=true,bool $sortresults = false) : string
{
    $files = [];
    $files = get_matching_files($dir,$allowed_extensions,true,true,$fileprefix,$excludefiles);
    if( $files === false ) return '';
    $out = "<select name=\"{$name}\" id=\"{$name}\" {$extratext}>\n";
    if( $allownone ) {
        $txt = '';
        if( empty($value) ) $txt = 'selected="selected"';
        $out .= "<option value=\"-1\" $txt>--- ".lang('none')." ---</option>\n";
    }

    if( $sortresults ) natcasesort($files);
    foreach( $files as $file ) {
        $txt = '';
        $opt = $file;
        if( !empty($optprefix) ) $opt = $optprefix.'/'.$file;
        if( $opt == $value ) $txt = 'selected="selected"';
        $out .= "<option value=\"{$opt}\" {$txt}>{$file}</option>\n";
    }
    $out .= '</select>';
    return $out;
}

/**
 * A function that, given the current request information will return
 * a pageid or an alias that should be used for the display
 * This method also handles matching routes and specifying which module
 * should be called with what parameters
 *
 * This is the main routine to do route dispatching
 *
 * @internal
 * @ignore
 * @access private
 * @return string (or null?)
 */
function get_pageid_or_alias_from_url()
{
    $config = cms_config::get_instance();

    $query_var = $config['query_var'];
    if( isset($_GET[$query_var]) ) {
        // using non friendly urls... get the page alias/id from the query var.
        $page = @trim((string) $_REQUEST[$query_var]);
    }
    else {
        // either we're using internal pretty urls or this is the default page.
        $page = '';
        if (isset($_SERVER['REQUEST_URI']) && !endswith($_SERVER['REQUEST_URI'], 'index.php')) {
            $matches = [];
            if (preg_match('/.*index\.php\/(.*?)$/', $_SERVER['REQUEST_URI'], $matches)) {
                // pretty urls... grab all the stuff after the index.php
                $page = trim($matches[1]);
            }
        }
    }
    unset($_GET['query_var']);

    $dflt_content = ContentOperations::get_instance()->GetDefaultContent();
    if( !$page ) {
        // by here, if page is empty, use the default page id
        return $dflt_content;
    }

    // by here, if we're not assuming pretty urls of any sort
    // and we have a value... we're done.
    if( $config['url_rewriting'] == 'none' ) return $page;

    // some kind of a pretty url.
    // strip off GET params.
    if( ($tmp = strpos($page,'?')) !== false ) $page = substr($page,0,$tmp);

    // strip off page extension
    if ($config['page_extension'] != '' && endswith($page, $config['page_extension'])) {
        $page = substr($page, 0, strlen($page) - strlen($config['page_extension']));
    }

    // trim trailing and leading /
    // it appears that some servers leave in the first / of a request some times which will stop rout matching.
    $page = trim($page, '/');

    // see if there's a route that matches.
    // note: we handle content routing separately at this time.
    // it'd be cool if contents were just another mact.
    $route = cms_route_manager::find_match($page);
    if( ! $route ) {
        // if no route matched... assume it is an alias and that the alias begins after the last /
        if( ($pos = strrpos($page,'/')) !== false ) $page = substr($page, $pos + 1);
    }
    else {
        if( $route['key1'] == '__CONTENT__' ) {
            // a route to a page.
            $page = (int)$route['key2'];
        }
        else {
            $matches = $route->get_results();

            // it's a module route... setup some default parameters.
            $arr = ['id'=>'cntnt01', 'action'=>'defaulturl', 'inline'=>false, 'module'=>$route->get_dest() ];
            $matches = array_merge( $arr, $matches );
            $tmp = $route->get_defaults();
            if( $tmp ) $matches = array_merge( $dflts, $matches );

            // Get rid of numeric matches, and put the data into the _REQUEST for later processing.
            foreach( $matches as $key=>$val ) {
                if( is_int($key) ) {
                    // do nothing
                }
                elseif( $key != 'id'/*&& $key != 'returnid' && $key != 'action' 2.3 deprecation, breaks lot of stuff*/ ) {
                    $_REQUEST[$matches['id'] . $key] = $val;
                }

            }

            // Put the resulting mact into the request for later processing.
            // this is essentially our translation from pretty URLs to non-pretty URLS.
            $_REQUEST['mact'] = $matches['module'] . ',' . $matches['id'] . ',' . $matches['action'] . ',' . $matches['inline'];

            // Get a decent returnid
            $page = $dflt_content;
            if( $matches['returnid'] ) {
                $page = (int) $matches['returnid'];
                unset( $matches['returnid'] );
            }
        }
    }

    return $page;
}

/**
 * Get javascript for initialization of the configured 'advanced'
 *  (a.k.a. wysiwyg) text-editor
 * @since 2.3
 * @param array $params  Configuration details. Recognized members are:
 *  bool   'edit'   whether the content is editable. Default false (i.e. just for display)
 *  string 'handle' name of the js variable to be used for the created editor. Default 'editor'
 *  string 'htmlid' id of the page-element whose content is to be edited. Mandatory.
 *  string 'style'  override for the normal editor theme/style.  Default ''
 *  string 'typer'  content-type identifier, an absolute filepath or at least
 *    an extension or pseudo (like 'smarty'). Default ''
 *  string 'workid' id of a div to be created (by some editors) to process
 *    the content of the htmlid-element. Default 'Editor'
 *
 * @return array up to 2 members, being 'head' and/or 'foot'
 */
function get_editor_script(array $params) : array
{
    $userid = get_userid();
	$vars = explode ('::', cms_userprefs::get_for_user($userid, 'syntax_editor'));
	$modname = $vars[0] ?? '';
	if( $modname ) {
		$modinst = cms_utils::get_module($modname);
		if( $modinst ) {
			if (empty($params['style'])) {
				$params['style'] = cms_userprefs::get_for_user($userid, 'editor_theme');
			}
			if( $modinst instanceof SyntaxEditor ) {
				$edname = $vars[1] ?? $modname;
				return $modinst->GetEditorScript($edname, $params);
			}
			elseif( $modinst->HasCapability(CmsCoreCapabilities::SYNTAX_MODULE) ) {
			   // TODO other modules ?
			   // c.f. cms_utils::get_syntax_highlighter_module()
			}
		}
	}
    return [];
}
