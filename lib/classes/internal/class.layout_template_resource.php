<?php
#CMS - CMS Made Simple
#(c)2004-2012 by Ted Kulp (wishy@users.sf.net)
#Visit our homepage at: http://www.cmsmadesimple.org
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
#along with this program; if not, write to the Free Software
#Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
#
#$Id: content.functions.php 6863 2011-01-18 02:34:48Z calguy1000 $

namespace CMSMS\internal;
use \CmsLayoutTemplateType;

/**
 * @package CMS
 */

/**
 * A simple class for handling layout templates as a resource.
 *
 * @package CMS
 * @author Robert Campbell
 * @internal
 * @ignore
 * @copyright Copyright (c) 2012, Robert Campbell <calguy1000@cmsmadesimple.org>
 * @since 1.12
 */
class layout_template_resource extends fixed_smarty_custom_resource
{
	private $_section;

	public function __construct($section = '')
	{
		if( in_array($section,array('top','head','body')) ) $this->_section = $section;
	}

	public function buildUniqueResourceName(\Smarty $smarty,$resource_name, $is_config = false)
	{
		return parent::buildUniqueResourceName($smarty,$resource_name,$is_config).'--'.$this->_section;
	}

	private function &get_template($name)
	{
		$obj = \CmsLayoutTemplate::load($name);
		$ret = new \StdClass;
		$ret->modified = $obj->get_modified();
		$ret->content = $obj->get_content();
		return $ret;
	}

	protected function fetch($name,&$source,&$mtime)
	{
		if( $name == 'notemplate' ) {
			$source = '{content}';
			$mtime = time(); // never cache...
			return;
		}
		else if( startswith($name,'appdata;') ) {
			$name = substr($name,8);
			$source = cms_utils::get_app_data($name);
			$mtime = time();
			return;
		}

		$source = '';
		$mtime = null;

		try {
			$tpl = $this->get_template($name);
			if( !is_object($tpl) ) return;
		}
		catch( Exception $e ) {
			cms_error('Missing Template: '.$name);
			return;
		}

		switch( $this->_section ) {
		case 'top':
			$mtime = $tpl->modified;
			$pos1 = stripos($tpl->content,'<head');
			$pos2 = stripos($tpl->content,'<header');
			if( $pos1 === FALSE || $pos1 == $pos2 ) return;
			$source = trim(substr($tpl->content,0,$pos1));
			return;

		case 'head':
			$mtime = $tpl->modified;
			$pos1 = stripos($tpl->content,'<head');
			$pos1a = stripos($tpl->content,'<header');
			$pos2 = stripos($tpl->content,'</head>');
			if( $pos1 === FALSE || $pos1 == $pos1a || $pos2 === FALSE ) return;
			$source = trim(substr($tpl->content,$pos1,$pos2-$pos1+7));
			return;

		case 'body':
			$mtime = $tpl->modified;
			$pos = stripos($tpl->content,'</head>');
			if( $pos !== FALSE ) {
				$source = trim(substr($tpl->content,$pos+7));
			}
			else {
				$source = $tpl->content;
			}
			return;

		default:
			$source = trim($tpl->content);
			$mtime = $tpl->modified;
			return;
		}
	}

} // end of class

#
# EOF
