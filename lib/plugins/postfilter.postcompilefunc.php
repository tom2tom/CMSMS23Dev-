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

use \CMSMS\HookManager;

function smarty_postfilter_postcompilefunc($tpl_output, $smarty)
{
	$result = explode(':', $smarty->_current_file);

	if (count($result) > 1)	{
		switch ($result[0])	{
        case 'cms_stylesheet':
        case 'stylesheet':
            HookManager::do_hook('Core::StylesheetPostCompile',array('stylesheet'=>&$tpl_output));
            break;

        case "content":
            HookManager::do_hook('Core::ContentPostCompile', array('content' => &$tpl_output));
            break;

        case 'cms_template':
        case "template":
        case 'tpl_top':
        case 'tpl_body':
        case 'tpl_head':
            HookManager::do_hook('Core::TemplatePostCompile',array('template'=>&$tpl_output,'type'=>$result[0]));
        break;

        default:
            break;
		}
	}

	HookManager::do_hook('Core::SmartyPostCompile', array('content' => &$tpl_output));

	return $tpl_output;
}
?>
