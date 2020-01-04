<?php
/*
CMSMS News module action: display a browsable category list.
Copyright (C) 2005-2020 CMS Made Simple Foundation <foundation@cmsmadesimple.org>
This file is a component of CMS Made Simple <http://www.cmsmadesimple.org>

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.
You should have received a copy of the GNU General Public License
along with this program. If not, see <https://www.gnu.org/licenses/>.
*/

use CMSMS\TemplateOperations;
use News\Utils;

if( !isset($gCms) ) exit;

if( isset($params['browsecattemplate']) ) {
  $template = trim($params['browsecattemplate']);
}
else {
  $me = $this->GetName();
  $tpl = TemplateOperations::get_default_template_by_type($me.'::browsecat');
  if( !is_object($tpl) ) {
    audit('',$me,'No usable categories-template found');
    return;
  }
  $template = $tpl->get_name();
}

$items = Utils::get_categories($id,$params,$returnid);

// Display template
$tpl = $smarty->createTemplate($this->GetTemplateResource($template),null,null,$smarty);

$tpl->assign('count', count($items))
 ->assign('cats', $items);

$tpl->display();
