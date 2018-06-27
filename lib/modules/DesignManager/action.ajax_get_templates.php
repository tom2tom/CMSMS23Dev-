<?php
# DesignManager module action: process ajax call to populate templates
# Copyright (C) 2012-2018 Robert Campbell <calguy1000@cmsmadesimple.org>
# This file is a component of CMS Made Simple <http://www.cmsmadesimple.org>
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

$handlers = ob_list_handlers();
for ($cnt = 0, $n = sizeof($handlers); $cnt < $n; $cnt++) { ob_end_clean(); }

try {
    $tmp = get_parameter_value($_REQUEST,'filter');
    $filter = json_decode($tmp,TRUE);
    $smarty->assign('tpl_filter',$filter);
    if( !$this->CheckPermission('Modify Templates') ) $filter[] = 'e:'.get_userid();

    $tpl_query = new CmsLayoutTemplateQuery($filter);
    $templates = $tpl_query->GetMatches();
    if( count($templates) ) {
        $smarty->assign('templates',$templates);
        $tpl_nav = [];
        $tpl_nav['pagelimit'] = $tpl_query->limit;
        $tpl_nav['numpages'] = $tpl_query->numpages;
        $tpl_nav['numrows'] = $tpl_query->totalrows;
        $tpl_nav['curpage'] = (int)($tpl_query->offset / $tpl_query->limit) + 1;
        $smarty->assign('tpl_nav',$tpl_nav);
    }

    $designs = CmsLayoutCollection::get_all();
    if( count($designs) ) {
        $smarty->assign('list_designs',$designs);
        $tmp = [];
        for( $i = 0; $i < count($designs); $i++ ) {
            $tmp['d:'.$designs[$i]->get_id()] = $designs[$i]->get_name();
            $tmp2[$designs[$i]->get_id()] = $designs[$i]->get_name();
        }
        $smarty->assign('design_names',$tmp2);
    }

    $types = CmsLayoutTemplateType::get_all();
    $originators = [];
    if( count($types) ) {
        $tmp = [];
        $tmp2 = [];
        $tmp3 = [];
        for( $i = 0; $i < count($types); $i++ ) {
            $tmp['t:'.$types[$i]->get_id()] = $types[$i]->get_langified_display_value();
            $tmp2[$types[$i]->get_id()] = $types[$i]->get_langified_display_value();
            $tmp3[$types[$i]->get_id()] = $types[$i];
            if( !isset($originators[$types[$i]->get_originator()]) ) {
                $originators['o:'.$types[$i]->get_originator()] = $types[$i]->get_originator(TRUE);
            }
        }
        $smarty->assign('list_all_types',$tmp3);
        $smarty->assign('list_types',$tmp2);
    }

    $locks = CmsLockOperations::get_locks('template');
    $smarty->assign('have_locks',$locks ? count($locks) : 0);
    $smarty->assign('lock_timeout', $this->GetPreference('lock_timeout'));
    $smarty->assign('coretypename',CmsLayoutTemplateType::CORE);
    $smarty->assign('manage_templates',$this->CheckPermission('Modify Templates'));
    $smarty->assign('manage_designs',$this->CheckPermission('Manage Designs'));
    $smarty->assign('has_add_right',
                    $this->CheckPermission('Modify Templates') ||
                    $this->CheckPermission('Add Templates'));

    echo $this->ProcessTemplate('ajax_get_templates.tpl');
}
catch( Exception $e ) {
    echo '<div class="error">'.$e->GetMessage().'</div>';
    // nothing here
}
exit;
