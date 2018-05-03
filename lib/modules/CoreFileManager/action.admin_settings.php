<?php
# CoreFileManager module action: admin_settings
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

if (!function_exists('cmsms')) {
    exit;
}
if (!$this->CheckPermission('Modify Site Preferences')) {
    //TODO relevant permission
    exit;
}

$advancedmode = $this->GetPreference('advancedmode', 0);
$showhiddenfiles = $this->GetPreference('showhiddenfiles', 0);
$showthumbnails = $this->GetPreference('showthumbnails', 1);
$iconsize = $this->GetPreference('iconsize', 0);
$permissionstyle = $this->GetPreference('permissionstyle', 'xxx');

//$smarty->assign('path',$this->CreateInputHidden($id,"path",$path)); //why?

$smarty->assign('advancedmode', $advancedmode);
$smarty->assign('showhiddenfiles', $showhiddenfiles);
$smarty->assign('showthumbnails', $showthumbnails);
$smarty->assign('create_thumbnails', $this->GetPreference('create_thumbnails', 1));
$iconsizes = [];
$iconsizes['32px'] = $this->Lang('largeicons').' (32px)';
$iconsizes['16px'] = $this->Lang('smallicons').' (16px)';
$smarty->assign('iconsizes', $iconsizes);
$smarty->assign('iconsize', $this->GetPreference('iconsize', '16px'));

$permstyles = [$this->Lang('rwxstyle')=>'xxxxxxxxx', $this->Lang('755style')=>'xxx'];
$smarty->assign('permstyles', array_flip($permstyles));
$smarty->assign('permissionstyle', $permissionstyle);

echo $this->ProcessTemplate('settings.tpl');