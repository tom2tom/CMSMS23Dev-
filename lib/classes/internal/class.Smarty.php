<?php
#Class to tailor Smarty for CMSMS
#Copyright (C) 2004-2012 Ted Kulp <ted@cmsmadesimple.org>
#Copyright (C) 2013-2018 CMS Made Simple Foundation <foundation@cmsmadesimple.org>
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

namespace CMSMS\internal;

use cms_config;
use cms_module_smarty_plugin_manager;
use CmsApp;
use CMSMS\SimplePluginOperations;
use Exception;
use LogicException;
use Smarty;
use Smarty_Internal_Template;
use SmartyException;
use const CMS_ADMIN_PATH;
use const CMS_ASSETS_PATH;
use const CMS_DEBUG;
use const CMS_ROOT_PATH;
use const SMARTY_SYSPLUGINS_DIR;
use const TMP_TEMPLATES_C_LOCATION;
use function cms_error;
use function get_userid;
use function is_sitedown;
use function startswith;

require_once(CMS_ROOT_PATH.'/lib/smarty/Smarty.class.php'); //or SmartyBC

/**
 * Extends the Smarty class for CMSMS.
 *
 * @package CMS
 * @since 0.1
 */
class CmsSmarty extends Smarty //OR SmartyBC? OR replicate some method-aliases from that class?
{
    private static $_instance = null;

    /**
     * Constructor
     * Although this is a singleton, the constructor must be public to conform with class ancestors
     */
    public function __construct()
    {
        parent::__construct();

        $this->direct_access_security = true;
        $this->assignGlobal('app_name','CMSMS');

        // set template compile dir
        $this->setCompileDir(TMP_TEMPLATES_C_LOCATION);

        if( CMS_DEBUG ) $this->error_reporting = 'E_ALL';

        // default template class
        $this->template_class = '\\CMSMS\\internal\\template_wrapper';

        // default plugin handler
        $this->registerDefaultPluginHandler( [ $this, 'defaultPluginHandler' ] );

        $this->addConfigDir(CMS_ASSETS_PATH.DIRECTORY_SEPARATOR.'configs');

        // common resources
        $this->registerResource('module_db_tpl',new module_db_template_resource())
             ->registerResource('module_file_tpl',new module_file_template_resource())
             ->registerResource('cms_file',new file_template_resource())
             ->registerResource('cms_template',new layout_template_resource())
             ->registerResource('cms_stylesheet',new layout_stylesheet_resource())
             ->registerResource('content',new content_template_resource());


        $this->addPluginsDir(CMS_ASSETS_PATH.DIRECTORY_SEPARATOR.'plugins') //plugin-assets prevail
             ->addPluginsDir(CMS_ROOT_PATH.DIRECTORY_SEPARATOR.'lib'.DIRECTORY_SEPARATOR.'plugins')
             ->addPluginsDir(CMS_ROOT_PATH.DIRECTORY_SEPARATOR.'plugins') // deprecated

             ->addTemplateDir(CMS_ASSETS_PATH.DIRECTORY_SEPARATOR.'templates')
             ->addTemplateDir(CMS_ROOT_PATH.DIRECTORY_SEPARATOR.'lib'.DIRECTORY_SEPARATOR.'assets'.DIRECTORY_SEPARATOR.'templates');

        $_gCms = CmsApp::get_instance();
        if( $_gCms->is_frontend_request() ) {
            // just for frontend actions
            global $CMS_INSTALL_PAGE;
            // Check if we are at install page, don't register anything if so, as nothing below is needed.
            if( isset($CMS_INSTALL_PAGE) ) return;

            if( is_sitedown() ) {
                $this->setCaching(false);
                $this->force_compile = true;
            }

            // Load resources
            $this->registerPlugin('compiler','content','\\CMSMS\internal\\page_template_parser::compile_fecontentblock',false)
                 ->registerPlugin('function','content_image','\\CMSMS\\internal\content_plugins::fetch_imageblock',false)
                 ->registerPlugin('function','content_module','\\CMSMS\\internal\\content_plugins::fetch_moduleblock',false)
                 ->registerPlugin('function','content_text','\\CMSMS\\internal\\content_plugins::fetch_textblock',false)
                 ->registerPlugin('function','process_pagedata','\\CMSMS\\internal\\content_plugins::fetch_pagedata',false);

            // Autoload filters
            $this->autoloadFilters();

            // Enable security object
            $config = cms_config::get_instance();
            if( !$config['permissive_smarty'] ) $this->enableSecurity('\\CMSMS\\internal\\smarty_security_policy');
        }
        elseif( $_gCms->test_state(CmsApp::STATE_ADMIN_PAGE) ) {
            $this->setCaching(false); //CHECKME
            $this->addConfigDir(CMS_ADMIN_PATH.DIRECTORY_SEPARATOR.'configs')
                 ->addPluginsDir(CMS_ADMIN_PATH.DIRECTORY_SEPARATOR.'plugins')
                 ->addTemplateDir(CMS_ADMIN_PATH.DIRECTORY_SEPARATOR.'templates');
        }
    }

    /**
     * get_instance method
     * @return object
     */
    final public static function &get_instance() : self
    {
        if( !self::$_instance ) self::$_instance = new self();
        return self::$_instance;
    }

    /* *
     * Load filters from CMSMS plugins folders
     */
    private function autoloadFilters()
    {
        $pre = [];
        $post = [];
        $output = [];

        foreach( $this->getPluginsDir() as $dir ) {
            if( !is_dir($dir) ) continue;

            $files = glob($dir.'*php');
            if( !$files ) continue;

            foreach( $files as $file ) {
                $parts = explode('.',basename($file));
                if( !is_array($parts) || count($parts) != 3 ) continue;

                switch( $parts[0] ) {
                case 'output':
                    $output[] = $parts[1];
                    break;

                case 'prefilter':
                    $pre[] = $parts[1];
                    break;

                case 'postfilter':
                    $post[] = $parts[1];
                    break;
                }
            }
        }

        $this->autoload_filters = ['pre'=>$pre,'post'=>$post,'output'=>$output];
    }

    public function registerClass($a,$b)
    {
        if( $this->security_policy ) $this->security_policy->static_classes[] = $a;
        parent::registerClass($a,$b);
    }

    /**
     * defaultPluginHandler
     * NOTE: Registered in constructor
     *
     * @param string $name
     * @param string $type
     * @param string $template
     * @param string $callback
     * @param string $script
     * @param bool   $cachable (set true by caller)
     * @return bool true on success, false on failure
     */
    public function defaultPluginHandler($name, $type, $template, &$callback, &$script, &$cachable)
    {
        // plugins including a smarty_* function
//        $cachable = true; //CHECKME & upstream sets this
        $base = $type.'.'.$name.'.php';
        $basef = $type.'_'.$name;

        // walk plugin dirs to try to find a matching plugin
        foreach ($this->getPluginsDir() as $dir) {
            $file = $dir.$base;
            if( !is_file($file) ) continue;

            require_once $file;

            foreach ([
            'smarty_',
            'smarty_cms_', // deprecated
            'smarty_nocache_', // deprecated
            ] as $pref ) {
                $func = $pref.$basef;
                if( !function_exists($func) ) continue;

                $callback = $func;
                $script = $file;
//TODO CHECKME plugins never cachable? smarty prevents this?
                $cachable = false;
                return true;
            }
        }

        if( $type != 'function' ) {
            return;
        }

//        if( CmsApp::get_instance()->is_frontend_request() ) {
            $row = cms_module_smarty_plugin_manager::load_plugin($name,$type);
            if( is_array($row) && is_array($row['callback']) && count($row['callback']) == 2 &&
                is_string($row['callback'][0]) && is_string($row['callback'][1]) ) {
                $callback = $row['callback'][0].'::'.$row['callback'][1];
//TODO CHECKME
                $cachable = false;
                return true;
            }

            // check if it is a simple plugin
            $res = SimplePluginOperations::get_instance()->load_plugin( $name );
            if( $res ) {
                $callback = $res;
//TODO CHECKME simple-plugins not actually called ?
//                $cachable = false;
                return true;
            }
//        }

        return false;
    }

    /**
     * Test if a smarty plugin with the specified name already exists.
     *
     * @param string the plugin name
     * @return bool
     */
    public function is_registered(string $name) : bool
    {
        return isset($this->registered_plugins['function'][$name]);
    }

    /**
     * Create a template object
     *
     * @param  string  $template   the resource handle of the template
     * @param  mixed   $cache_id   optional cache id to be used with this template
     * @param  mixed   $compile_id optional compile id to be used with this template
     * @param  object  $parent     optional next-higher level of Smarty variables
     * @param  bool    $do_clone   optional flag whether to clone the Smarty object
     * @return Smarty_Internal_Template template object
     * @throws LogicException
     */
    public function createTemplate($template, $cache_id = null, $compile_id = null, $parent = null, $do_clone = true)
    {
        if( !(startswith($template,'eval:') || startswith($template,'string:') || startswith($template,'cmsfile:')) ) {
            if( ($pos = strpos($template,'*')) > 0 ) throw new LogicException("$template is an invalid CMSMS resource specification");
            if( ($pos = strpos($template,'/')) > 0 ) throw new LogicException("$template is an invalid CMSMS resource specification");
        }
        return parent::createTemplate($template, $cache_id, $compile_id, $parent, $do_clone );
    }

    /**
     * Return content for an error page
     *
     * @author Stikki
     * @param Exception object $e
     * @param bool $show_trace Optional flag whether to include a backtrace in the displayed report. Default true
     * @return string
     */
    public function errorConsole(Exception $e, bool $show_trace = true) : string
    {
        $this->force_compile = true;

        # do not show smarty debug console popup to users not logged in
        //$this->debugging = get_userid(false);
        $this->assign('e_line', $e->getLine())
             ->assign('e_file', $e->getFile())
             ->assign('e_message', $e->getMessage())
             ->assign('loggedin', get_userid(false));
		if( $show_trace ) {
            $this->assign('e_trace', htmlentities($e->getTraceAsString()));
		}
		else {
            $this->assign('e_trace', null);
		}

        // put mention into the admin log
        cms_error('Smarty Error: '. substr( $e->getMessage(),0 ,200 ) );

        $output = $this->fetch('cmsms-error-console.tpl');

        $this->force_compile = false;
        $this->debugging = false;

        return $output;
    }
} // class

class_alias('CMSMS\internal\CmsSmarty', 'CMSMS\internal\Smarty', false);
