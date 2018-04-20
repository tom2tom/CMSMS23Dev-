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

$CMS_ADMIN_PAGE=1;
$CMS_ADMIN_TITLE = 'system_verification';
$orig_memory = (function_exists('memory_get_usage')?memory_get_usage():0);

check_login();

require_once dirname(__DIR__).DIRECTORY_SEPARATOR.'lib'.DIRECTORY_SEPARATOR.'include.php';
$urlext='?'.CMS_SECURE_PARAM_NAME.'='.$_SESSION[CMS_USER_KEY];
@set_time_limit(9999); // this may not work on all hosts

$userid = get_userid();
$access = check_permission($userid, "Modify Site Preferences");

$themeObject = cms_utils::get_theme_object();

if (!$access) {
//TODO some immediate popup  lang('needpermissionto', '"Modify Site Preferences"')
	return;
}

define('CMS_BASE', dirname(__DIR__));
require_once cms_join_path(CMS_BASE, 'lib', 'test.functions.php');

function checksum_lang($params,&$smarty)
{
  if( isset($params['key']) ) return lang($params['key']);
}

function check_checksum_data(&$report)
{
  if( (!isset($_FILES['cksumdat'])) || empty($_FILES['cksumdat']['name']) ) {
    $report = lang('error_nofileuploaded');
    return false;
  }
  else if( $_FILES['cksumdat']['error'] > 0 ) {
    $report = lang('error_uploadproblem');
    return false;
  }
  else if( $_FILES['cksumdat']['size'] == 0 ) {
    $report = lang('error_uploadproblem');
    return false;
  }

  $fh = fopen($_FILES['cksumdat']['tmp_name'],'r');
  if( !$fh ) {
    $report = lang('error_uploadproblem');
    return false;
  }

  $config = cms_config::get_instance();
  $salt = md5_file($config['root_path']."/lib/version.php").md5_file($config['root_path']."/index.php");
  $filenotfound = array();
  $notreadable = 0;
  $md5failed = 0;
  $filesfailed = array();
  $filespassed = 0;
  $errorlines = 0;
  while( !feof($fh) ) {
      // get a line
      $line = fgets($fh,4096);

      // strip out comments
      $pos = strpos($line,'#');
      if( $pos !== FALSE ) $line = substr($line,0,$pos);

      // trim the line
      $line = trim($line);

      // skip empty line
      if( empty($line) ) continue;

      // split it into fields
      if( strstr($line,'--::--') === FALSE ) {
          $errorlines++;
          continue;
      }
      list($md5sum,$file) = explode('--::--',$line,2);

      if( !$md5sum || !$file ) {
          $errorlines++;
          continue;
      }

      $md5sum = trim($md5sum);
      $file = trim($file);

      $fn = cms_join_path($config['root_path'],$file);
      if( !file_exists( $fn ) ) {
          $filenotfound[] = $file;
          continue;
      }

      if( is_dir( $fn ) ) continue;

      if( !is_readable( $fn ) ) {
          $notreadable++;
          continue;
      }

      $md5 = md5($salt.md5_file($fn));
      if( !$md5 ) {
          $md5failed++;
          continue;
      }

      if( $md5sum != $md5 )  $filesfailed[] = $file;

      // it passed.
      $filespassed++;
  }
  fclose($fh);

  if( $filespassed == 0 || count($filenotfound) || $errorlines || $notreadable || $md5failed || count($filesfailed) ) {
    // build the error report
    $tmp2 = array();
    if( $filespassed == 0 )  $tmp2[] = lang('no_files_scanned');
    if( $errorlines ) $tmp2[] = lang('lines_in_error',$errorlines);
    if( count($filenotfound) ) $tmp2[] = sprintf("%d %s",count($filenotfound),lang('files_not_found'));
    if( $notreadable ) $tmp2[] = sprintf("%d %s",$notreadable,lang('files_not_readable'));
    if( $md5failed ) $tmp2[] = sprintf("%d %s",$md5failed,lang('files_checksum_failed'));
    if( !empty($tmp) ) $tmp .= "<br />";

    $tmp = implode( "<br />", $tmp2 );
    if( count($filenotfound) ) {
      $tmp .= "<br />".lang('files_not_found').':';
      $tmp .= "<br />".implode("<br />",$filenotfound)."<br />";
    }
    if( count($filesfailed) ) {
      $tmp .= "<br />".count($filesfailed).' '.lang('files_failed').':';
      $tmp .= "<br />".implode("<br />",$filesfailed)."<br />";
    }

    $report = $tmp;
    return false;
  }

  return true;
}


function generate_checksum_file(&$report)
{
  $config = cms_config::get_instance();
  $output = '';
  $salt = md5_file($config['root_path']."/lib/version.php").md5_file($config['root_path']."/index.php");

  $excludes = array('^\.svn' , '^CVS$' , '^\#.*\#$' , '~$', '\.bak$', '^uploads$', '^tmp$', '^captchas$' );
  $tmp = get_recursive_file_list( $config['root_path'], $excludes);
  if( count($tmp) <= 1 ) {
    $report = lang('error_retrieving_file_list');
    return false;
  }

  foreach( $tmp as $file ) {
    if( is_dir($file) ) continue;
    $md5sum = md5($salt.md5_file($file));
    $file = str_replace($config['root_path'],'',$file);
    $output .= "{$md5sum}--::--{$file}\n";
  }

  $handlers = ob_list_handlers();
  for ($cnt = 0; $cnt < sizeof($handlers); $cnt++) { ob_end_clean(); }
  header('Pragma: public');
  header('Expires: 0');
  header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
  header('Cache-Control: private',false);
  header('Content-Description: File Transfer');
  header('Content-Type: text/plain');
  header("Content-Disposition: attachment; filename=\"checksum.dat\"" );
  header('Content-Transfer-Encoding: binary');
  header('Content-Length: ' . strlen($output));
  echo $output;
  exit;
}

$smarty = CMSMS\internal\Smarty::get_instance();
// Get ready
$smarty->register_function('lang','checksum_lang');
$smarty->force_compile = true;

// Handle output
$res = true;
$report = '';
if( isset($_POST['action']) ) {
  switch($_POST['action']) {
  case 'upload':
    $res = check_checksum_data($report);
    if( $res === true ) $themeObject->RecordMessage('success', lang('checksum_passed'));
    break;
  case 'download':
    $res = generate_checksum_file($report);
    break;
  }
}

if( !$res ) $themeObject->RecordMessage('error', $report);

$smarty->assign('urlext',$urlext);

// Display the output
include_once 'header.php';
$smarty->display('checksum.tpl');
include_once 'footer.php';
