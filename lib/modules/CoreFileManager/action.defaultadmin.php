<?php
# CoreFileManager module action: defaultadmin
# Copyright (C) 2018 The CMSMS Dev Team <coreteam@cmsmadesimple.org>
# This file is a component of CMS Made Simple <http://www.cmsmadesimple.org>
#
# This program is free software; you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation; either version 3 of the License, or
# (at your option) any later version.
#
# This program is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
# You should have received a copy of the GNU General Public License
# along with this program. If not, see <https://www.gnu.org/licenses/>.

require_once __DIR__.DIRECTORY_SEPARATOR.'action.getlist.php';

// breadcrumbs

if ($FM_PATH) {
    $u = $this->create_url($id, 'defaultadmin', $returnid, ['p'=>'']);
    //root
    $oneset = new stdClass();
    $oneset->name = $this->Lang('top');
    $oneset->url = $u;
    $items = [$oneset];
    //rest
    $t = '';
    $segs = explode(DIRECTORY_SEPARATOR, $FM_PATH);
    $c = count($segs);
    for ($i=0; $i<$c; ++$i) {
        $oneset = new stdClass();
        $oneset->name = fm_enc(fm_convert_win($segs[$i]));
        if ($i > 0) $t .= DIRECTORY_SEPARATOR;
        $t .= $segs[$i];
        $oneset->url = $u.rawurlencode($t);
        $items[] = $oneset;
    }
    $smarty->assign('crumbs', $items);
    $t = dirname($FM_PATH);
    if ($t == '.') {$t = '';} else {$t = rawurlencode($t);}
    $smarty->assign('parent_url', $u.$t);
}

$smarty->assign('crumbjoiner', 'if-angle-double-right'); //or 'if-angle-double-left' for 'rtl'

// permitted operations

if ($profile->can_upload) {
    $smarty->assign('pupload', 1);
}
if ($profile->can_mkdir) {
    $smarty->assign('pmkdir', 1);
}
if ($profile->can_delete) {
    $smarty->assign('pdel', 1);
}

// folders tree

$smarty->assign('browse', $this->Lang('browse'));
$t = fm_dir_tree($FM_ROOT_PATH, (($FM_PATH) ? $pathnow : ''));
$smarty->assign('treeview', $t);

// tailor the compression UI

$items = fm_get_arch_picker($this);
$smarty->assign('archtypes', $items);
if (count($items) > 1) {
    $t = $this->Lang('compress_sel');
}else {
    $t = $this->Lang('compress_typed', reset($items)['label']);
}
$smarty->assign('title_compress', $t);

$smarty->assign('form_start', $this->CreateFormStart($id, 'fileaction', $returnid, 'post', '', false, '', ['p'=> rawurlencode($FM_PATH)]));
$baseurl = $this->GetModuleURLPath();

// page infrastructure

$u = $this->create_url($id, 'fileaction', $returnid, ['p'=>$FM_PATH]);
$action_url = rawurldecode(str_replace('&amp;', '&', $u).'&cmsjobtype=1');
$u = $this->create_url($id, 'getlist', $returnid, ['p'=>$FM_PATH, 'ajax'=>1]);
$refresh_url = rawurldecode(str_replace('&amp;', '&', $u).'&cmsjobtype=1');

//TODO $FM_ROOT_PATH
$here = $FM_PATH;

//<link rel="stylesheet" href="{$baseurl}/lib/css/jquery.dm-uploader.css">
$css = <<<EOS
<link rel="stylesheet" href="{$baseurl}/lib/css/filemanager.css">

EOS;
$this->AdminHeaderContent($css);

$js = <<<EOS
<script src="{$baseurl}/lib/js/jquery.SSsort+metadata.min.js"></script>
<script src="{$baseurl}/lib/js/jquery.treemenu.min.js"></script>
<script src="{$baseurl}/lib/js/jquery.easysearch.js"></script>
<script src="{$baseurl}/lib/js/jquery.dm-uploader.js"></script>
<script>
//<![CDATA[

EOS;
$t = file_get_contents(cms_join_path(__DIR__, 'lib', 'js', 'defaultadmin.inc.js'));
// included js may include variables enclosed in markers '~%' and '%~'.
// like $varname or lang|key or lang|key,param[,param2 ...] Such $varname's must all be 'used' here
$js .= preg_replace_callback('/~%(.+?)%~/', function ($match) use ($id, $action_url, $refresh_url, $here)
{
 $name = $match[1];
 if ($name[0] == '$') {
    $name = substr($name, 1);
    $adbg = $$name;
    return $$name;
 } elseif (strncmp($name,'lang|',5) == 0) {
    $name = substr($name, 5);
    if (strpos($name,',') === false) {
       return $this->Lang($name);
    } else {
       $parts = explode(',',$name);
       return $this->Lang(...$parts);
    }
 } else {
    return '';
 }
}, $t);

$js .= <<<EOS
//]]>
</script>

EOS;
$this->AdminBottomContent($js);

echo $this->ProcessTemplate('defaultadmin.tpl');
