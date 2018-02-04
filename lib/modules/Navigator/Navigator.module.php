<?php
#BEGIN_LICENSE
#-------------------------------------------------------------------------
# Module: Navigator (c) 2013 by Robert Campbell
#         (calguy1000@cmsmadesimple.org)
#  An module for CMS Made Simple to allow building hierarchical navigations.
#
#-------------------------------------------------------------------------
# CMS - CMS Made Simple is (c) 2005 by Ted Kulp (wishy@cmsmadesimple.org)
# This file is a component of CMS Made Simple <http://www.cmsmadesimple.org>
#
#-------------------------------------------------------------------------
#
# This program is free software; you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation; either version 2 of the License, or
# (at your option) any later version.
#
# This program is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
# You should have received a copy of the GNU General Public License
# along with this program. If not, see <https://www.gnu.org/licenses/>.
#
#-------------------------------------------------------------------------
#END_LICENSE
#$Id$

class NavigatorNode
{
    /**
     * This little function will remove all silly notices in smarty.
     */
    public function __get($key) { return null; }
}

final class Navigator extends CMSModule
{
    const __DFLT_PAGE = '**DFLT_PAGE**';

    public function GetName() { return get_class($this); }
    public function GetFriendlyName() { return $this->Lang('friendlyname'); }
    public function IsPluginModule() { return true; }
    public function HasAdmin() { return false; }
    public function GetVersion() { return '1.0.7'; }
    public function MinimumCMSVersion() { return '2.1.99'; }
    public function GetAdminDescription() { return $this->Lang('description'); }
    public function GetAdminSection() { return 'layout'; }
    public function LazyLoadFrontend() { return TRUE; }
    public function LazyLoadAdmin() { return TRUE; }
    public function GetHelp($lang='en_US') { return $this->Lang('help'); }
    public function GetAuthor() { return 'Robert Campbell'; }
    public function GetAuthorEmail() { return 'calguy1000@cmsmadesimple.org'; }
    public function GetChangeLog() { return file_get_contents(dirname(__FILE__).'/changelog.inc'); }

    public function InitializeFrontend()
    {
//2.3 does nothing        $this->RestrictUnknownParams();
        $this->SetParameterType('items',CLEAN_STRING);
        $this->SetParameterType('nlevels',CLEAN_INT);
        $this->SetParameterType('number_of_levels',CLEAN_INT);
        $this->SetParameterType('show_all',CLEAN_INT);
        $this->SetParameterType('show_root_siblings',CLEAN_INT);
        $this->SetParameterType('start_element',CLEAN_STRING); // yeah, it's a string
        $this->SetParameterType('start_page',CLEAN_STRING);
        $this->SetParameterType('start_text',CLEAN_STRING);
        $this->SetParameterType('start_level',CLEAN_INT);
        $this->SetParameterType('template',CLEAN_STRING);
        $this->SetParameterType('childrenof',CLEAN_STRING);
        $this->SetParameterType('loadprops',CLEAN_INT);
        $this->SetParameterType('collapse',CLEAN_INT);
        $this->SetParameterType('root',CLEAN_STRING);
        $this->SetParameterType('excludeprefix',CLEAN_STRING);
        $this->SetParameterType('includeprefix',CLEAN_STRING);
    }

    public function InitializeAdmin()
    {
        $this->CreateParameter('items', 'contact,home', $this->lang('help_items'));
        $this->CreateParameter('nlevels', '1', $this->lang('help_nlevels'));
        $this->CreateParameter('number_of_levels', '1', $this->lang('help_number_of_levels'));
        $this->CreateParameter('show_all', '0', $this->lang('help_show_all'));
        $this->CreateParameter('show_root_siblings', '1', $this->lang('help_show_root_siblings'));
        $this->CreateParameter('start_element', '1.2', $this->lang('help_start_element'));
        $this->CreateParameter('start_page', '', $this->lang('help_start_page'));
        $this->CreateParameter('start_text', '', $this->lang('help_start_text'));
        $this->CreateParameter('start_level', '', $this->lang('help_start_level'));
        $this->CreateParameter('template', '', $this->lang('help_template'));
        $this->CreateParameter('childrenof','',$this->Lang('help_childrenof'));
        $this->CreateParameter('action','',$this->Lang('help_action'));
        $this->CreateParameter('loadprops','',$this->Lang('help_loadprops'));
        $this->CreateParameter('collapse','',$this->Lang('help_collapse'));
        $this->CreateParameter('root','',$this->Lang('help_root2'));
        $this->CreateParameter('includeprefix','',$this->Lang('help_includeprefix'));
        $this->CreateParameter('excludeprefix','',$this->Lang('help_excludeprefix'));
    }

    final public static function nav_breadcrumbs($params,&$smarty)
    {
        $params['action'] = 'breadcrumbs';
        $params['module'] = __CLASS__;
        return cms_module_plugin($params,$smarty);
    }

    final public static function page_type_lang_callback($str)
    {
        $mod = cms_utils::get_module(__CLASS__);
        if( is_object($mod) ) return $mod->Lang('type_'.$str);
    }

    public static function reset_page_type_defaults(CmsLayoutTemplateType $type)
    {
        if( $type->get_originator() != __CLASS__ ) throw new CmsLogicException('Cannot reset contents for this template type');

        $fn = null;
        switch( $type->get_name() ) {
        case 'navigation':
            $fn = 'simple_navigation.tpl';
            break;
        case 'breadcrumbs':
            $fn = 'dflt_breadcrumbs.tpl';
            break;
        }

        $fn = cms_join_path(dirname(__FILE__),'templates',$fn);
        if( file_exists($fn) ) return @file_get_contents($fn);
    }

    public static function template_help_callback($str)
    {
        $str = trim($str);
        $mod = cms_utils::get_module('Navigator');
        if( is_object($mod) ) {
            $file = $mod->GetModulePath().'/doc/tpltype_'.$str.'.inc';
            if( is_file($file) ) return file_get_contents($file);
        }
    }

} // End of class
