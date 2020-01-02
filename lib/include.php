<?php
#setup classes, includes etc for request processing
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

//use CMSMS\internal\ModulePluginOperations;

use CMSMS\AppState;
use CMSMS\AuditOperations;
use CMSMS\Database\DatabaseConnectionException;
use CMSMS\Events;
use CMSMS\internal\global_cachable;
use CMSMS\internal\global_cache;
use CMSMS\internal\ModulePluginOperations;
use CMSMS\ModuleOperations;
use CMSMS\NlsOperations;

/**
 * This file is included in every page.  It does all setup functions including
 * importing additional functions/classes, setting up sessions and nls, and
 * construction of various important variables like $gCms.
 *
 * This file is not intended for use by third party applications to create access to CMSMS API's.
 * It is intended for and supported for use in core CMSMS operations only.
 *
 * @package CMS
 */

define('CONFIG_FILE_LOCATION', dirname(__DIR__).DIRECTORY_SEPARATOR.'config.php');

$dirpath = __DIR__.DIRECTORY_SEPARATOR;
// include some stuff
require_once $dirpath.'classes'.DIRECTORY_SEPARATOR.'class.AppState.php';
if (isset($CMS_APP_STATE)) {
    AppState::add_state($CMS_APP_STATE);
}
$installing = AppState::test_state(AppState::STATE_INSTALL);
if (!$installing && (!is_file(CONFIG_FILE_LOCATION) || filesize(CONFIG_FILE_LOCATION) < 100)) {
    die('FATAL ERROR: config.php file not found or invalid');
}

require_once $dirpath.'version.php'; // some defines
require_once $dirpath.'classes'.DIRECTORY_SEPARATOR.'class.cms_config.php';
require_once $dirpath.'classes'.DIRECTORY_SEPARATOR.'class.CmsException.php';
require_once $dirpath.'misc.functions.php'; //some used in defines setup
require_once $dirpath.'defines.php'; //populate relevant defines
require_once $dirpath.'classes'.DIRECTORY_SEPARATOR.'class.CmsApp.php'; //used in autoloader
require_once $dirpath.'module.functions.php'; //some used in autoloader
require_once $dirpath.'autoloader.php';
require_once $dirpath.'vendor'.DIRECTORY_SEPARATOR.'autoload.php'; //CHECKME Composer support on production system ?
require_once $dirpath.'compat.functions.php';
require_once $dirpath.'page.functions.php';

if (isset($_REQUEST[CMS_JOB_KEY])) {
    // since 2.3 value 0|1|2 indicates the type of request, hence appropriate inclusions
    $type = (int)$_REQUEST[CMS_JOB_KEY];
    $CMS_JOB_TYPE = min(max((int)$type, 0), 2);
} elseif (isset($CMS_JOB_TYPE)) {
    $CMS_JOB_TYPE = min(max((int)$CMS_JOB_TYPE, 0), 2);
} elseif (
    // undocumented, deprecated, output-suppressors
    (isset($_REQUEST['showtemplate']) && $_REQUEST['showtemplate'] == 'false')
    || isset($_REQUEST['suppressoutput'])) {
    $CMS_JOB_TYPE = 1;
} else {
    //normal output
    $CMS_JOB_TYPE = 0;
}
CmsApp::get_instance()->JOBTYPE = $CMS_JOB_TYPE;

if ($CMS_JOB_TYPE < 2) {
    require_once $dirpath.'placement.functions.php';
    require_once $dirpath.'translation.functions.php';
}

debug_buffer('Finished loading basic files');

if (!isset($_SERVER['REQUEST_URI']) && isset($_SERVER['QUERY_STRING'])) {
    $_SERVER['REQUEST_URI'] = $_SERVER['PHP_SELF'] . '?' . $_SERVER['QUERY_STRING'];
}
// sanitize $_SERVER and $_GET
cleanArray($_SERVER);
cleanArray($_GET);

// Grab the current configuration & some define's
$_app = CmsApp::get_instance(); // for use in this file only.
$config = $_app->GetConfig();
AuditOperations::init(); // load some audit-methods & audit-classes which won't autoload

// Set the timezone
if ($config['timezone']) @date_default_timezone_set(trim($config['timezone']));

if ($config['debug']) {
    @ini_set('display_errors',1);
    @error_reporting(E_ALL);
}

if (cms_to_bool(ini_get('register_globals'))) {
    die('FATAL ERROR: For security reasons register_globals must not be enabled for any CMSMS install.  Please adjust your PHP configuration settings to disable this feature.');
}

$administering = AppState::test_state(AppState::STATE_ADMIN_PAGE);
if ($administering) {
    setup_session();

// TODO is this $CMS_JOB_TYPE-dependant ?
    function cms_admin_sendheaders($content_type = 'text/html',$charset = '')
    {
        // Language shizzle
        if (!$charset) $charset = NlsOperations::get_encoding();
        header("Content-Type: $content_type; charset=$charset");
    }
}

cms_siteprefs::setup();

// deprecated since 2.3 useless
$obj = new global_cachable('schema_version', function()
    {
        return $CMS_SCHEMA_VERSION; //NULL during installation!
    });
global_cache::add_cachable($obj);
$obj = new global_cachable('modules', function()
    {
        $db = CmsApp::get_instance()->GetDb();
        $query = 'SELECT * FROM '.CMS_DB_PREFIX.'modules';
        return $db->GetAssoc($query); // Keyed by module_name
     });
global_cache::add_cachable($obj);
$obj = new global_cachable('module_deps', function()
    {
        $db = CmsApp::get_instance()->GetDb();
        $query = 'SELECT parent_module,child_module,minimum_version FROM '.CMS_DB_PREFIX.'module_deps ORDER BY parent_module';
        $tmp = $db->GetArray($query);
        if (!is_array($tmp) || !$tmp) return '-';  // special value so that we actually return something to cache.
        $out = [];
        foreach( $tmp as $row) {
            $out[$row['child_module']][$row['parent_module']] = $row['minimum_version'];
        }
        return $out;
    });
global_cache::add_cachable($obj);

if ($CMS_JOB_TYPE < 2) {
    $obj = new global_cachable('latest_content_modification', function()
        {
            $db = CmsApp::get_instance()->GetDb();
            $query = 'SELECT modified_date FROM '.CMS_DB_PREFIX.'content ORDER BY modified_date DESC';
            $tmp = $db->GetOne($query);
            return $db->UnixTimeStamp($tmp);
        });
    global_cache::add_cachable($obj);
    $obj = new global_cachable('default_content', function()
        {
            $db = CmsApp::get_instance()->GetDb();
            $query = 'SELECT content_id FROM '.CMS_DB_PREFIX.'content WHERE default_content = 1';
            return $db->GetOne($query);
        });
    global_cache::add_cachable($obj);

    // the pages flat list
    $obj = new global_cachable('content_flatlist', function()
        {
            $query = 'SELECT content_id,parent_id,item_order,content_alias,active FROM '.CMS_DB_PREFIX.'content ORDER BY hierarchy';
            $db = CmsApp::get_instance()->GetDb();
            return $db->GetArray($query);
        });
    global_cache::add_cachable($obj);

    // hence the tree
    $obj = new global_cachable('content_tree', function()
        {
            $flatlist = global_cache::get('content_flatlist');
            $tree = cms_tree_operations::load_from_list($flatlist);
            return $tree;
        });
    global_cache::add_cachable($obj);

    // hence the flat/quick list
    $obj = new global_cachable('content_quicklist', function()
        {
            $tree = global_cache::get('content_tree');
            return $tree->getFlatList();
        });
    global_cache::add_cachable($obj);
}

// other global caches
Events::setup();
ModulePluginOperations::setup();

// Attempt to override the php memory limit
if (isset($config['php_memory_limit']) && !empty($config['php_memory_limit'])) ini_set('memory_limit',trim($config['php_memory_limit']));

// Load them into the usual variables.  This'll go away a little later on.
if (!$installing) {
    try {
        debug_buffer('Initialize database');
        $_app->GetDb();
        debug_buffer('Finished initializing database');
    }
    catch( DatabaseConnectionException $e) {
        die('Sorry, something has gone wrong.  Please contact a site administrator. <em>('.get_class($e).')</em>');
    }
}

// Fix for IIS (and others) to make sure REQUEST_URI is filled in
if (!isset($_SERVER['REQUEST_URI'])) {
    $_SERVER['REQUEST_URI'] = $_SERVER['SCRIPT_NAME'];
    if (isset($_SERVER['QUERY_STRING'])) $_SERVER['REQUEST_URI'] .= '?'.$_SERVER['QUERY_STRING'];
}

if (!$installing) {
    // Set a umask
    $global_umask = cms_siteprefs::get('global_umask','');
    if ($global_umask != '') umask( octdec($global_umask));

    $modops = ModuleOperations::get_instance();
    // After autoloader & modules
    $tmp = $modops->GetCapableModules(CmsCoreCapabilities::JOBS_MODULE);
    if( $tmp ) {
        $mod_obj = $modops->get_module_instance($tmp[0]); //NOTE not $modinst !
        $_app->jobmgrinstance = $mod_obj; //cache it
        if ($CMS_JOB_TYPE == 0) {
            $callback = $tmp[0].'::begin_async_work';
            Events::AddDynamicHandler('Core', 'PostRequest', $callback);
        }
    }
}

if ($CMS_JOB_TYPE < 2) {
    // Setup language stuff.... will auto-detect languages (launch only to admin at this point)
    if ($administering) {
        NlsOperations::set_language();
    }

    if (!$installing) {
        debug_buffer('Initialize Smarty');
        $smarty = $_app->GetSmarty();
        debug_buffer('Finished initializing Smarty');
        $smarty->assignGlobal('sitename', cms_siteprefs::get('sitename', 'CMSMS Site'));
    }
}

if (!$installing) {
    require_once($dirpath.'classes'.DIRECTORY_SEPARATOR.'internal'.DIRECTORY_SEPARATOR.'class_compatibility.php');
}
