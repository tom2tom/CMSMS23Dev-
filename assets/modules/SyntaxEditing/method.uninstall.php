<?php
/*
SyntaxEditing module method: uninstallation
Copyright (C) 2019-2020 Tom Phane <tomph@cmsmadesimple.org>
This file is a component of the RichEditing module for CMS Made Simple
 <http://dev.cmsmadesimple.org/projects/syntaxedit>

This file is free software; you can redistribute it and/or modify it
under the terms of the GNU Affero General Public License as published by
the Free Software Foundation; either version 3 of the License, or
(at your option) any later version.

This file is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU Affero General Public License for more details.
<https://www.gnu.org/licenses/#AGPL>
*/

if (!isset($gCms)) exit;

$this->RemovePreference();

$me = $this->GetName();
$val = cms_siteprefs::get('syntax_editor');

if ($val == $me) {
    cms_siteprefs::set('syntax_editor','');
    cms_siteprefs::set('syntax_type','');
    cms_siteprefs::set('syntax_theme','');
}

$users = UserOperations::get_instance()->GetList();
foreach ($users as $uid => $uname) {
    $val = cms_userprefs::get_for_user($uid, 'syntax_editor');
    if ($val == $me) {
        cms_userprefs::set_for_user($uid, 'syntax_editor', '');
        cms_userprefs::set_for_user($uid, 'syntax_type', '');
        cms_userprefs::set_for_user($uid, 'syntax_theme', '');
    }
}

//TODO un-register handlers for events which allow user-preferences-change related to this module