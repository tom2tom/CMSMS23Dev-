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

function smarty_function_sitename($params, &$smarty)
{
	$result = get_site_preference('sitename', 'CMSMS Site');

	if( isset($params['assign']) ) {
        $smarty->assign(trim($params['assign']),$result);
        return;
    }
	return $result;
}

function smarty_cms_about_function_sitename()
{
?>
        <p>Author: Ted Kulp &lt;ted@cmsmadesimple.org&gt;</p>

        <p>Change History:</p>
        <ul>
			<li>None</li>
        </ul>
<?php
}
?>