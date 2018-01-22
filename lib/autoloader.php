<?php
#autoloader for CMS Made Simple <http://www.cmsmadesimple.org>
#(c)2004-2010 by Ted Kulp (wishy@users.sf.net)
#(c)2010-2018 The CMSMS Dev Team <@#cmsmadesimple.org> 
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
#$Id$

/**
 * @package CMS
 * @ignore
 */

/**
 * A function for auto-loading classes.
 *
 * @since 1.7
 * @internal
 * @ignore
 * @param string A possibly-namespaced class name
 */
function cms_autoloader(string $classname)
{
	$root = CMS_ROOT_PATH.DIRECTORY_SEPARATOR.'lib'.DIRECTORY_SEPARATOR.'classes'.DIRECTORY_SEPARATOR;
	// standard content types (prioritized)
	$fp = $root.'contenttypes'.DIRECTORY_SEPARATOR.$classname.'.inc.php';
	if (is_file($fp)) {
		require_once $fp;
		return;
	}

	$o = ($classname[0] != '\\') ? 0 : 1;
	$p = strpos($classname, '\\', $o + 1);
	if ($p !== false) {
		$space = substr($classname, $o, $p - $o);
		if ($space == 'CMSMS') {
			$sroot = $root;
		} else {
			//CHECKME module supposed to be loaded, if a related class is used !? if lazy ??
			if (!class_exists($space, false)) { //CHECKME nested autoload ok here?
				return;
			}
			//multiple module-places in 2.3+
			$sroot = false;
			$bp = dirname(__DIR__).DIRECTORY_SEPARATOR; //or CMS_ROOT_PATH...
			$fn = DIRECTORY_SEPARATOR.$space.DIRECTORY_SEPARATOR.$space.'.module.php';

			foreach ([
				'lib'.DIRECTORY_SEPARATOR.'modules',
				'assets'.DIRECTORY_SEPARATOR.'modules',
				'modules', //deprecated place
			] as $path) {
				if (is_file($bp.$path.$fn)) {
					$sroot = $bp.$path.DIRECTORY_SEPARATOR.$space.DIRECTORY_SEPARATOR.'lib'.DIRECTORY_SEPARATOR;
					break;
				}
			}
			if (!$sroot) {
				return;
			}
		}
		$path = str_replace('\\', DIRECTORY_SEPARATOR, substr($classname, $p + 1));
		$classname = basename($path);
		$path = dirname($path);
		if ($path != '.') {
			$sroot .= $path.DIRECTORY_SEPARATOR;
		}
		foreach (['class.', 'trait.', 'interface.', ''] as $test) {
			$fp = $sroot.$test.$classname.'.php';
			if (is_file($fp)) {
				require_once $fp;
				return;
			}
		}

		if (endswith($classname, 'Task')) {
			if ($space == 'CMSMS') {
				$sroot = CMS_ROOT_PATH.DIRECTORY_SEPARATOR.'lib'.DIRECTORY_SEPARATOR.'tasks'.DIRECTORY_SEPARATOR;
			}
			$t2 = substr($classname, 0, -4).'.task';
			foreach (['class.', 'trait.', 'interface.', ''] as $test) {
				$fp = $sroot.$test.$t2.'.php';
				if (is_file($fp)) {
					require_once $fp;
					return;
				}
				$fp = $sroot.$test.$classname.'.php';
				if (is_file($fp)) {
					require_once $fp;
					return;
				}
			}
		}
		return; //failed
	} elseif ($o) {
		return;
	}

	if (strpos($classname, 'Smarty') !== false) {
		if (strpos($classname, 'CMS') === false) {
			return; //hand over to smarty autoloader
		}
	}

	// standard classes
	$fp = $root.'class.'.$classname.'.php';
	if (is_file($fp)) {
		require_once $fp;
		return;
	}

	// standard internal classes - all are spaced

	// lowercase classes
	$lowercase = strtolower($classname);
	$fp = $root.'class.'.$lowercase.'.inc.php';
	if (is_file($fp)) {
		require_once $fp;
		return;
	}

	// lowercase internal classes - all are spaced

	// standard interfaces
	$fp = $root.'interface.'.$classname.'.php';
	if (is_file($fp)) {
		require_once $fp;
		return;
	}

	// internal interfaces
	$fp = $root.'internal'.DIRECTORY_SEPARATOR.'interface.'.$classname.'.php';
	if (is_file($fp)) {
		require_once $fp;
		return;
	}

	// standard tasks
	if (endswith($classname, 'Task')) {
		$class = substr($classname, 0, -4);
		$fp = CMS_ROOT_PATH.DIRECTORY_SEPARATOR.'lib'.DIRECTORY_SEPARATOR.'tasks'.DIRECTORY_SEPARATOR.'class.'.$class.'.task.php';
		if (is_file($fp)) {
			require_once $fp;
			return;
		}
	}

	// module classes
	$modops = ModuleOperations::get_instance();
	$fp = $modops->get_module_filename($classname);
	if ($fp && is_file($fp)) {
		//deprecated - some modules require existence of this, or assume, and actually use it
		$gCms = CmsApp::get_instance();
		require_once $fp;
		return;
	}

	// unspaced loaded-module-ancillary classes
	$modules = $modops->GetLoadedModules();
	if ($modules) {
		foreach (array_keys($modules) as $modname) {
			$root = $modops->get_module_path($modname);
			foreach (['class.', 'trait.', 'interface.', ''] as $test) {
				$fp = $root.DIRECTORY_SEPARATOR.'lib'.DIRECTORY_SEPARATOR.$test.$classname.'.php';
				if (is_file($fp)) {
					require_once $fp;
					return;
				}
			}
		}
	}
}

spl_autoload_register('cms_autoloader');

