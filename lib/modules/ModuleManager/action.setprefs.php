<?php
#BEGIN_LICENSE
#-------------------------------------------------------------------------
# Module: ModuleManager (c) 2008 by Robert Campbell
#         (calguy1000@cmsmadesimple.org)
#  An addon module for CMS Made Simple to allow browsing remotely stored
#  modules, viewing information about them, and downloading or upgrading
#
#-------------------------------------------------------------------------
# CMS - CMS Made Simple is (c) 2005 by Ted Kulp (wishy@cmsmadesimple.org)
# Visit our homepage at: http://www.cmsmadesimple.org
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
if( !isset($gCms) ) exit;
if( !$this->CheckPermission('Modify Site Preferences' ) ) return;

$this->SetCurrentTab('prefs');

if( isset($config['developer_mode']) && !empty($params['reseturl']) ) {
    $this->SetPreference('module_repository',ModuleManager::_dflt_request_url);
    $this->SetMessage($this->Lang('msg_urlreset'));
    $this->RedirectToAdminTab();
}
if( isset($params['dl_chunksize']) ) $this->SetPreference('dl_chunksize',(int)trim($params['dl_chunksize']));
$latestdepends = (int)get_parameter_value($params,'latestdepends');
$this->SetPreference('latestdepends',$latestdepends);


if( isset($config['developer_mode']) ) {
    if( isset($params['url']) ) $this->SetPreference('module_repository',trim($params['url']));
    $disable_caching = (int)get_parameter_value($params,'disable_caching');
    $this->SetPreference('disable_caching',$disable_caching);
    $this->SetPreference('allowuninstall',(int)get_parameter_value($params,'allowuninstall'));
}
else {
    $this->SetPreference('allowuninstall',0);
}

$this->SetMessage($this->Lang('msg_prefssaved'));
$this->RedirectToAdminTab();
?>
