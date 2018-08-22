<?php
#Search: a module to find words/phrases in 'core' site pages and some modules' pages
#Copyright (C) 2004-2018 Ted Kulp <ted@cmsmadesimple.org>
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

use CMSMS\Events;
use Search\Command\ReindexCommand;
use Search\Utils;

define( 'NON_INDEXABLE_CONTENT', '<!-- pageAttribute: NotSearchable -->' );

class Search extends CMSModule
{
    public function GetAdminDescription() { return $this->Lang('description'); }
    public function GetAuthor() { return 'Ted Kulp'; }
    public function GetAuthorEmail() { return 'ted@cmsmadesimple.org'; }
    public function GetChangeLog() { return @file_get_contents(__DIR__.DIRECTORY_SEPARATOR.'changelog.inc'); }
    public function GetEventDescription( $eventname ) { return $this->lang('eventdesc-' . $eventname); }
    public function GetEventHelp( $eventname ) { return $this->lang('eventhelp-' . $eventname); }
    public function GetFriendlyName() { return $this->Lang('search'); }
    public function GetHelp($lang='en_US') { return $this->Lang('help'); }
    public function GetName() { return 'Search'; }
    public function GetVersion() { return '1.52'; }
    public function HandlesEvents () { return true; }
    public function HasAdmin() { return true; }
    public function IsPluginModule() { return true; }
    public function LazyLoadAdmin() { return true; }
    public function LazyLoadFrontend() { return true; }
    public function MinimumCMSVersion() { return '2.2.900'; }
    public function VisibleToAdminUser() { return $this->CheckPermission('Modify Site Preferences'); }

    public function InitializeAdmin()
    {
        $this->CreateParameter('action','default',$this->Lang('param_action'));
        $this->CreateParameter('count','null',$this->Lang('param_count'));
        $this->CreateParameter('detailpage','null',$this->Lang('param_detailpage'));
        $this->CreateParameter('formtemplate','',$this->Lang('param_formtemplate'));
        $this->CreateParameter('inline','false',$this->Lang('param_inline'));
        $this->CreateParameter('modules','null',$this->Lang('param_modules'));
        $this->CreateParameter('pageid','null',$this->Lang('param_pageid'));
        $this->CreateParameter('passthru_*','null',$this->Lang('param_passthru'));
        $this->CreateParameter('resultpage', 'null', $this->Lang('param_resultpage'));
        $this->CreateParameter('resulttemplate','',$this->Lang('param_resulttemplate'));
        $this->CreateParameter('search_method','get',$this->Lang('search_method'));
        $this->CreateParameter('searchtext','null',$this->Lang('param_searchtext'));
        $this->CreateParameter('submit',$this->Lang('searchsubmit'),$this->Lang('param_submit'));
        $this->CreateParameter('use_or','true',$this->Lang('param_useor'));
    }

    public function InitializeFrontend()
    {
//2.3 does nothing        $this->RestrictUnknownParams();
        $this->SetParameterType('count',CLEAN_INT);
        $this->SetParameterType('detailpage',CLEAN_STRING);
        $this->SetParameterType('formtemplate',CLEAN_STRING);
        $this->SetParameterType('inline',CLEAN_STRING);
        $this->SetParameterType('modules',CLEAN_STRING);
        $this->SetParameterType('origreturnid',CLEAN_INT);
        $this->SetParameterType('pageid',CLEAN_INT);
        $this->SetParameterType('resultpage',CLEAN_STRING);
        $this->SetParameterType('resulttemplate',CLEAN_STRING);
        $this->SetParameterType('search_method',CLEAN_STRING);
        $this->SetParameterType('searchinput',CLEAN_STRING);
        $this->SetParameterType('searchtext',CLEAN_STRING);
        $this->SetParameterType('submit',CLEAN_STRING);
        $this->SetParameterType('use_or',CLEAN_INT);
        $this->SetParameterType(CLEAN_REGEXP.'/passthru_.*/',CLEAN_STRING);
    }

    protected function GetSearchHtmlTemplate()
    {
        return '
{$startform}
<label for="{$search_actionid}searchinput">{$searchprompt}:&nbsp;</label><input type="text" class="search-input" id="{$search_actionid}searchinput" name="{$search_actionid}searchinput" size="20" maxlength="50" placeholder="{$searchtext}"/>
{*
<br/>
<input type="checkbox" name="{$search_actionid}use_or" value="1"/>
*}
<button type="submit" name="submit" class="adminsubmit icon do search-button">{$submittext}</button>
{if isset($hidden)}{$hidden}{/if}
{$endform}';
    }

    protected function GetResultsHtmlTemplate()
    {
        $text = <<<EOT
<h3>{\$searchresultsfor} &quot;{\$phrase}&quot;</h3>
{if \$itemcount > 0}
<ul>
  {foreach from=\$results item=entry}
  <li>{\$entry->title} - <a href="{\$entry->url}">{\$entry->urltxt}</a> ({\$entry->weight}%)</li>
  {*
     You can also instantiate custom behavior on a module by module basis by looking at
     the \$entry->module and \$entry->modulerecord fields in \$entry
      ie: {if \$entry->module == 'News'}{News action='detail' article_id=\$entry->modulerecord detailpage='News'}
  *}
  {/foreach}
</ul>

<p>{\$timetaken}: {\$timetook}</p>
{else}
  <p><strong>{\$noresultsfound}</strong></p>
{/if}
EOT;
        return $text;
    }

    protected function DefaultStopWords()
    {
        return $this->Lang('default_stopwords');
    }

    public function RemoveStopWordsFromArray($words)
    {
        $stop_words = preg_split("/[\s,]+/", $this->GetPreference('stopwords', $this->DefaultStopWords()));
        return array_diff($words, $stop_words);
    }

    public function StemPhrase($phrase)
    {
        return Utils::StemPhrase($this,$phrase);
    }

    public function AddWords($module = 'Search', $id = -1, $attr = '', $content = '', $expires = NULL)
    {
        return Utils::AddWords($this,$module,$id,$attr,$content,$expires);
    }

    public function DeleteWords($module = 'Search', $id = -1, $attr = '')
    {
        return Utils::DeleteWords($this,$module,$id,$attr);
    }

    public function DeleteAllWords($module = 'Search', $id = -1, $attr = '')
    {
        $db = $this->GetDb();
        $db->Execute('TRUNCATE '.CMS_DB_PREFIX.'module_search_index');
        $db->Execute('TRUNCATE '.CMS_DB_PREFIX.'module_search_items');

        Events::SendEvent( 'Search', 'SearchAllItemsDeleted' );
    }

    public function Reindex()
    {
        return Utils::Reindex($this);
    }

    public function RegisterEvents()
    {
        $this->AddEventHandler( 'Core', 'ContentEditPost', false );
        $this->AddEventHandler( 'Core', 'ContentDeletePost', false );
        $this->AddEventHandler( 'Core', 'AddTemplatePost', false );
        $this->AddEventHandler( 'Core', 'EditTemplatePost', false );
        $this->AddEventHandler( 'Core', 'DeleteTemplatePost', false );
        $this->AddEventHandler( 'Core', 'ModuleUninstalled', false );
    }

    public function DoEvent($originator,$eventname,&$params)
    {
        return Utils::DoEvent($this, $originator, $eventname, $params);
    }

    public function HasCapability($capability,$params = [])
    {
        switch( $capability ) {
        case CmsCoreCapabilities::SEARCH_MODULE:
        case CmsCoreCapabilities::PLUGIN_MODULE:
        case 'clicommands':
            return true;
        }
        return false;
    }

    public static function page_type_lang_callback($str)
    {
        $mod = cms_utils::get_module('Search');
        if( is_object($mod) ) return $mod->Lang('type_'.$str);
    }

    public static function reset_page_type_defaults(CmsLayoutTemplateType $type)
    {
        if( $type->get_originator() != 'Search' ) throw new CmsLogicException('Cannot reset contents for this template type');

        $mod = cms_utils::get_module('Search');
        if( !is_object($mod) ) return;
        switch( $type->get_name() ) {
        case 'searchform':
            return $mod->GetSearchHtmlTemplate();
        case 'searchresults':
            return $mod->GetResultsHtmlTemplate();
        }
    }

    public function get_cli_commands( $app )
    {
        if( ! $app instanceof \CMSMS\CLI\App ) throw new LogicException(__METHOD__.' Called from outside of cmscli');
        if( !class_exists('\\CMSMS\\CLI\\GetOptExt\\Command') ) throw new LogicException(__METHOD__.' Called from outside of cmscli');

        $out = [];
        $out[] = new ReindexCommand( $app );
        return $out;
    }
} // class
