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
#but WITHOUT ANY WARRANTY; without even the implied warranty of
#MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
#GNU General Public License for more details.
#You should have received a copy of the GNU General Public License
#along with this program. If not, see <https://www.gnu.org/licenses/>.
#
#$Id$

$CMS_ADMIN_PAGE=1;
require_once dirname(__DIR__).DIRECTORY_SEPARATOR.'lib'.DIRECTORY_SEPARATOR.'include.php';

$urlext='?'.CMS_SECURE_PARAM_NAME.'='.$_SESSION[CMS_USER_KEY];
check_login();
$config = \cms_config::get_instance();

// get some urls and preset language strings.

$data = array();
$data['ajax_help_url'] = 'ajax_help.php'.$urlext;
$data['ajax_alerts_url'] = 'ajax_alerts.php'.$urlext;
$data['title_help'] = lang('help');
$data['lang_alert'] = lang('alert');
$data['lang_error'] = lang('error');
$data['lang_ok'] = lang('ok');
$data['lang_cancel'] = lang('cancel');
$data['lang_confirm'] = lang('confirm');
$data['lang_yes'] = lang('yes');
$data['lang_no'] = lang('no');
$data['lang_none'] = lang('none');
$data['lang_disabled'] = lang('disabled');
$data['lang_hierselect_title'] = lang('title_hierselect_select');
$data['lang_select_file'] = lang('select_file');
$data['lang_choose'] = lang('choose');
$data['lang_filetobig'] = lang('upload_filetobig');
$data['lang_largeupload'] = lang('upload_largeupload');
$data['max_upload_size'] = $config['max_upload_size'];
$data['admin_url'] = $config['admin_url'];
$data['root_url'] = $config['root_url'];
$data['uploads_url'] = $config['uploads_url'];
$data['secure_param_name'] = CMS_SECURE_PARAM_NAME;
$data['user_key'] = $_SESSION[CMS_USER_KEY];

// todo: use  apreference
$fp = ModuleOperations::get_instance()->GetFilePickerModule();
if( $fp ) {
    $data['filepicker_url'] = $fp->get_browser_url();
    $data['filepicker_url'] = str_replace('&amp;','&',$data['filepicker_url']).'&showtemplate=false';
}

// output some javascript
$out = 'cms_data = {};'."\n";

foreach( $data as $key => $value ) {
    $value = json_encode($value);
    $out .= "cms_data.{$key} = {$value};\n";
}

$out .= <<<EOT
function cms_lang(key) {
    'use strict';
    key = 'lang_'+key;
    if( typeof(cms_data[key]) !== 'undefined' ) return cms_data[key];
    alert('lang key '+key+' notset');
}

// a silly shiv for IE11. ... remove me ASAP.
if (!String.prototype.startsWith) {
    String.prototype.startsWith = function(searchString, position){
      position = position || 0;
      return this.substr(position, searchString.length) === searchString;
  };
}
EOT;
header('Pragma: public');
header('Expires: 0');
header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
header('Cache-Control: private',false);
header('Content-type: text/javascript');
echo $out;
exit;
?>