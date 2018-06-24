<?php
/*
CoreFileManager module action: view or edit or display properties of a file
Copyright (C) 2018 The CMSMS Dev Team <coreteam@cmsmadesimple.org>
This file is a component of CMS Made Simple <http://www.cmsmadesimple.org>

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.
You should have received a copy of the GNU General Public License
along with this program. If not, see <https://www.gnu.org/licenses/>.
*/
if (!isset($gCms)) exit;
$pdev = $this->CheckPermission('Modify Site Code') || !empty($config['developer_mode']);
if (!($pdev || $this->CheckPermission('Modify Files'))) exit;

$CFM_ROOTPATH = ($pdev) ? CMS_ROOT_PATH : $config['uploads_path'];
$CFM_RELPATH = $params['p'] ?? '';

$path = $CFM_ROOTPATH;
if ($CFM_RELPATH) {
    $path .= DIRECTORY_SEPARATOR . $CFM_RELPATH;
}
if (!is_dir($path)) { //CHECKME link to a dir ok?
    $path = $CFM_ROOTPATH;
    $CFM_RELPATH = '';
}

if (isset($params['close'])) {
    $this->Redirect($id, 'defaultadmin', '', ['p'=>$CFM_RELPATH]);
}

// various globals used downstream
global $CFM_IS_WIN, $helper;
$CFM_IS_WIN = DIRECTORY_SEPARATOR == '\\';
$helper = new \CMSMS\FileTypeHelper($config);

global $bytename, $kbname, $mbname, $gbname; //$tbname
$bytename = $this->Lang('bb');
$kbname = $this->Lang('kb');
$mbname = $this->Lang('mb');
$gbname = $this->Lang('gb');
//$tbname = $this->Lang('tb');

require_once __DIR__.DIRECTORY_SEPARATOR.'function.filemanager.php';

if (isset($params['view'])) {
    $file = cfm_clean_path($params['view']);
    $edit = false; //in case of text-display
} elseif (isset($params['edit'])) {
    $file = cfm_clean_path($params['edit']);
    $edit = true;
} else {
    $file = ' '.DIRECTORY_SEPARATOR; //trigger error
}

$fullpath = $path . DIRECTORY_SEPARATOR . $file;
if ($file == '' || !is_file($fullpath)) {
    $this->SetError($this->Lang('err_nofile'));
    $this->Redirect($id, 'defaultadmin', '', ['p'=>$CFM_RELPATH]);
}

if ($edit) {
    if (isset($params['apply']) || isset($params['submit'])) {
		$lvl = error_reporting(0);
        $res = file_put_contents($fullpath, $params['content'], LOCK_EX);
		error_reporting($lvl);
        if (isset($params['submit'])) {
			if ($res === false) {
                $this->SetError($this->Lang('err_save'));
			}
            $this->Redirect($id, 'defaultadmin', '', ['p'=>$CFM_RELPATH]);
        }
		if ($res === false) {
             $this->ShowErrors($this->Lang('err_save'));
		}
    }
}

$is_arch = false;
$is_image = false;
$is_audio = false;
$is_video = false;
$is_text = false;
$filenames = false; // for archive
$content = $params['content'] ?? null; // for text

if ($helper->is_archive($fullpath)) {
    $is_arch = true;
    $type = 'archive';
//    $filenames = cfm_get_archive_info($fullpath); //TODO
} elseif ($helper->is_image($fullpath)) {
    $is_image = true;
    $type = 'image';
} elseif ($helper->is_audio($fullpath)) {
    $is_audio = true;
    $type = 'audio';
} elseif ($helper->is_video($fullpath)) {
    $is_video = true;
    $type = 'video';
} elseif ($helper->is_text($fullpath)) {
    $is_text = true;
    $type = 'text';
    if ($content === null) {
		$content = file_get_contents($fullpath);
	}
} else {
    $type = 'file';
}
$smarty->assign('ftype', $type);
$smarty->assign('content', $content);

$file_url = cms_admin_utils::path_to_url($fullpath);
$smarty->assign('file_url', $file_url);

$items = [];
$items[] = '<a href="?p={urlencode($CFM_RELPATH)}&ampdl={urlencode($file)}"><i class="if-download" title="'.$this->Lang('download').'"></i></a>';
/* TODO
if (!$CFM_READONLY && $is_arch) {
    $zip_name = pathinfo($fullpath, PATHINFO_FILENAME);
    $items[] = '<a href="?p={urlencode($CFM_RELPATH)}&ampunzip={urlencode($file)}"><i class="if-resize-full" title="'.$this->Lang('expand').'"></i></a>'
}
*/
if (/*!$CFM_READONLY && */$pdev && $is_text && !$edit) {
    $items[] = '<a href="?p={urlencode(trim($CFM_RELPATH))}&ampedit={urlencode($file)}"><i class="if-edit" title="'.$this->Lang('edit').'"></i></a>';
}
$smarty->assign('acts', $items);

$items = [];
$items[$this->Lang($type)] = cfm_enc($file);
$items[$this->Lang('info_path')] = ($CFM_RELPATH) ? cfm_enc(cfm_convert_win($CFM_RELPATH)) : $this->Lang('top');
$filesize = filesize($fullpath);
$items[$this->Lang('info_size')] = cfm_get_filesize($filesize);
$items[$this->Lang('info_mime')] = cfm_get_mime_type($fullpath);

if ($is_arch && $filenames) {
    $total_files = 0;
    $total_uncomp = 0;
    foreach ($filenames as $fn) {
        if (!$fn['folder']) {
            ++$total_files;
        }
        $total_uncomp += $fn['filesize'];
    }
    $items[$this->Lang('info_archcount')] = $total_files;
    $items[$this->Lang('info_archsize')] = cfm_get_filesize($total_uncomp);
} elseif ($is_image) {
    $image_size = getimagesize($fullpath);
    if (!empty($image_size[0]) || !empty($image_size[1])) {
        $items[$this->Lang('info_image')] = ($image_size[0] ?? '0') . ' x ' . ($image_size[1] ?? '0');
    } else {
        $smarty->assign('setsize', 1); //force svg size
    }
} elseif ($is_text) {
    if (preg_match("//u", $content)) {
        $enc = 'UTF-8'; // string includes some UTF-8
    } elseif (function_exists('mb_detect_encoding')) {
        $enc = mb_detect_encoding($content, mb_detect_order(), true);
    } else {
        $enc = '?';
    }
    $items[$this->Lang('info_charset')] = $enc;
}
$smarty->assign('about', $items);

if (!$edit) {
    $smarty->assign('start_form', $this->CreateFormStart($id, 'open', $returnid, 'post', '', false, '',
      ['p'=>$CFM_RELPATH, 'view'=>$params['view']]));
}

$baseurl = $this->GetModuleURLPath();
$css = <<<EOS
<link rel="stylesheet" href="{$baseurl}/lib/css/filemanager.css">

EOS;
$this->AdminHeaderContent($css);

if ($is_text) {
    if ($edit) {
        $fixed = 'false';
        $smarty->assign('edit', 1);
        $smarty->assign('start_form', $this->CreateFormStart($id, 'open', $returnid, 'post', '', false, '',
            ['p'=>$CFM_RELPATH, 'edit'=>$params['edit']]));
        $smarty->assign('reporter', CmsFormUtils::create_input([
         'type'=>'textarea',
         'name'=>'content',
         'modid'=>$id,
         'htmlid'=>'reporter',
         'style'=>'display:none;',
        ]));
    } else {
        $fixed = 'true';
    }

	//TODO consider a site-preference for cdn
	$version = get_site_preference('aceversion', '1.3.3'); //TODO const etc
	$style = cms_userprefs::get_for_user(get_userid(false), 'editortheme');
	if (!$style) {
		$style = get_site_preference('editortheme', 'clouds');
//TODO      $style = $this->GetPreference('editortheme', 'clouds');
	}
	$style = strtolower($style);


    $js = <<<EOS
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/ace/$version/ace.js"></script>
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/ace/$version/ext-modelist.js"></script>
<script type="text/javascript">
//<![CDATA[
var editor = ace.edit("Editor");
(function () {
 var modelist = ace.require("ace/ext/modelist");
 var mode = modelist.getModeForPath("$fullpath").mode;
 editor.session.setMode(mode);
}());
editor.setOptions({
 readOnly: $fixed,
 autoScrollEditorIntoView: true,
 showPrintMargin: false,
 maxLines: Infinity,
 fontSize: '100%'
});
editor.renderer.setOptions({
 showGutter: false,
 displayIndentGuides: false,
 showLineNumbers: false,
 theme: "ace/theme/$style"
});

EOS;
    if ($edit) {
		//CHECKME any content validation relevant?
        $js .= <<<EOS
$(document).ready(function() {
 $('form').on('submit', function(ev) {
  $('#reporter').val(editor.session.getValue());
 });
});

EOS;
     }
     $js .= <<<EOS
//]]>
</script>

EOS;
    $this->AdminBottomContent($js);
} //is text

echo $this->ProcessTemplate('open.tpl');
