<?php
#Plugin to create elements for a CMSMS form start
#Copyright (C) 2004-2020 CMS Made Simple Foundation <foundation@cmsmadesimple.org>
#Thanks to Ted Kulp and all other contributors from the CMSMS Development Team.
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

use CMSMS\AppState;

function smarty_function_form_start($params, $template)
{
	//cuz this form will be POST'd, we don't use secure mact parameters
	$mactparms = [];
	$mactparms['module'] = $template->getTemplateVars('_module');
	$mactparms['mid'] = $template->getTemplateVars('actionid');
	$mactparms['returnid'] = $template->getTemplateVars('returnid');
	$mactparms['inline'] = 0;

	$tagparms = [
	'method' => 'post',
	'enctype' => 'multipart/form-data',
	];
	$gCms = CmsApp::get_instance();
	if( AppState::test_state(AppState::STATE_LOGIN_PAGE) ) {
		$tagparms['action'] = 'login.php';
	}
	else if( AppState::test_state(AppState::STATE_ADMIN_PAGE) ) {
		// check if it's a module action
		if( $mactparms['module'] ) {
			$tmp = $template->getTemplateVars('_action');
			if( $tmp ) $mactparms['action'] = $tmp;

			$tagparms['action'] = 'lib/moduleinterface.php';
			if( empty($mactparms['action']) ) $mactparms['action'] = 'defaultadmin';
			$mactparms['returnid'] = '';
			if( empty($mactparms['mid']) ) $mactparms['mid'] = 'm1_';
		}
	}
	else if( $gCms->is_frontend_request() ) {
		if( $mactparms['module'] ) {
			$tmp = $template->getTemplateVars('actionparams');
			if( is_array($tmp) && isset($tmp['action']) ) $mactparms['action'] = $tmp['action'];

			$tagparms['action'] = 'lib/moduleinterface.php';
			if( !$mactparms['returnid'] ) $mactparms['returnid'] = CmsApp::get_instance()->get_content_id();
			$hm = $gCms->GetHierarchyManager();
			$node = $hm->find_by_tag('id',$mactparms['returnid']);
			if( $node ) {
				$content_obj = $node->getContent();
				if( $content_obj ) $tagparms['action'] = $content_obj->GetURL();
			}
		}
	}

	$parms = [];
	foreach( $params as $key => $value ) {
		switch( $key ) {
		case 'module':
		case 'action':
		case 'mid':
		case 'returnid':
		case 'inline':
			$mactparms[$key] = trim($value);
			break;

		case 'inline':
			$mactparms[$key] = (bool) $value;
			break;

		case 'prefix':
			$mactparms['mid'] = trim($value);
			break;

		case 'method':
			$tagparms[$key] = strtolower(trim($value));
			break;

		case 'url':
			$key = 'action';
			if( dirname($value) == '.' ) {
				$config = $gCms->GetConfig();
				$value = $config['admin_url'].'/'.trim($value);
			}
			$tagparms[$key] = trim($value);
			break;

		case 'enctype':
		case 'id':
		case 'class':
			$tagparms[$key] = trim($value);
			break;

		case 'extraparms':
			if( $value ) {
				foreach( $value as $key=>$value2 ) {
					$parms[$key] = $value2;
				}
			}
			break;

		case 'assign':
			break;

		default:
			if( startswith($key,'form-') ) {
				$key = substr($key,5);
				$tagparms[$key] = $value;
			} else {
				$parms[$key] = $value;
			}
			break;
		}
	}

	$out = '<form';
	foreach( $tagparms as $key => $value ) {
		if( $value ) {
			$out .= " $key=\"$value\"";
		} else {
			$out .= " $key";
		}
	}
	$out .= '><div class="hidden">';
	if( $mactparms['module'] && $mactparms['action'] ) {
		$mact = $mactparms['module'].','.$mactparms['mid'].','.$mactparms['action'].','.(int)$mactparms['inline'];
		$out .= '<input type="hidden" name="mact" value="'.$mact.'" />';
		if( $mactparms['returnid'] != '' ) {
			$out .= '<input type="hidden" name="'.$mactparms['mid'].'returnid" value="'.$mactparms['returnid'].'" />';
		}
	}
	if( !$gCms->is_frontend_request() ) {
		if( !isset($mactparms['returnid']) || $mactparms['returnid'] == '' ) {
			if( isset( $_SESSION[CMS_USER_KEY] ) ) {
				$out .= '<input type="hidden" name="'.CMS_SECURE_PARAM_NAME.'" value="'.$_SESSION[CMS_USER_KEY].'" />';
			}
		}
	}
	foreach( $parms as $key => $value ) {
		$out .= '<input type="hidden" name="'.$mactparms['mid'].$key.'" value="'.$value.'" />';
	}
	$out .= '</div>';

	if( isset($params['assign']) ) {
		$template->assign(trim($params['assign']),$out);
		return;
	}
	return $out;
}

