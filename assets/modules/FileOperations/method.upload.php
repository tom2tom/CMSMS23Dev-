<?php
/*
FileOperations light-module method - process a file-upload
Copyright (C) 2021 CMS Made Simple Foundation <foundation@cmsmadesimple.org>

This file is a component of CMS Made Simple <http://www.cmsmadesimple.org>

CMS Made Simple is free software; you may redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of that license, or
(at your option) any later version.

CMS Made Simple is distributed in the hope that it will be useful, but
WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of that license along with CMS Made Simple.
If not, see <https://www.gnu.org/licenses/>.
*/

use CMSMS\FileType;
use CMSMS\FileTypeHelper;
use FileOperations\Utils;

header('Content-type:application/json;charset=utf-8');

try {
    $key = $params['filefield'] ?? ''; // input[type=file] HTMLElement name
    if (!$key) {
        throw new RuntimeException('Invalid parameters');
    }
    $f = &$_FILES[$key];
    // undefined | $_FILES corruption attack
    // If this request falls under any of them, treat as invalid
    if (!isset($f['error']) || is_array($f['error'])) {
        throw new RuntimeException('Invalid parameters');
    }

    switch ($f['error']) {
        case UPLOAD_ERR_OK:
            break;
        case UPLOAD_ERR_NO_FILE:
            throw new RuntimeException('No file sent');
        case UPLOAD_ERR_INI_SIZE:
        case UPLOAD_ERR_FORM_SIZE:
            throw new RuntimeException('Exceeded filesize limit');
        default:
            throw new RuntimeException('Unknown error');
    }

    //$topdir, $fullpath, $profile are set in parent code
    $maxsize = $config['max_upload_size'];
    $helper = new FileTypeHelper();
    $mime = $helper->get_file_type_mime($profile->type);
    $destpath = null;

    // crappy $_FILES[] arrangement forces these funcs
    $fileval = function($key, $idx = null) use($f)
    {
       return ($idx === null) ? $f[$key] : $f[$key][$idx];
    };
    $do_file = function($idx = null) use($f, $fileval, $topdir, $fullpath, $profile, $maxsize, $helper, $mime, &$destpath)
    {
        // Check filesize
        if ($maxsize > 0 && $fileval('size', $idx) > $maxsize) {
            throw new RuntimeException('File exceeded size limit');
        }

        $tmppath = $fileval('tmp_name', $idx);
        // Check content
        if ($profile->type == FileType::IMAGE) {
            if( function_exists('exif_imagetype') ) {
                if (!exif_imagetype($tmppath)) {
                    if (1) { //TODO extra check needed e.g. svg files
                        $here = 1; //DEBUG
                    } else {
                        throw new RuntimeException('Invalid file type');
                    }
                }
            }
            //else fallbacks
        }
        // else others TODO

        $fn = $fileval('name', $idx);
        $testpath = cms_join_path($fullpath, $fn);
        $destpath = Utils::clean_path($topdir, $testpath);
        if (!$destpath) {
            throw new RuntimeException('Invalid path: '.$testpath);
        }
        $destpath = Utils::lower_extension($destpath);

        if (is_dir($destpath) || is_file($destpath)) {
            throw new RuntimeException($fn.': '.$this->Lang('error_ajax_fileexists'));
        }

        if (move_uploaded_file($tmppath, $destpath)) {
            // Check file name, extension (tho the ext might be faked)
            if (!$profile->is_file_name_acceptable($destpath)) {
                unlink($destpath);
                throw new RuntimeException($fn.': '.$this->Lang('error_upload_acceptFileTypes'));
            }
            // Check mimetype (maybe dodgy, depending on installed capabilities)
            $filemime = $helper->get_mime_type($destpath);
            if (!startswith($filemime, 'text/html;')) { // skip if the check found nothing recognisable
                if (!$helper->match_mime($filemime, $mime)) {
                    unlink($destpath);
                    throw new RuntimeException($fn.': '.$this->Lang('error_upload_type', $fn));
                }
            }
            $filemode = get_server_permissions()[1]; // read+write
            chmod($destpath, $filemode);
            if ($profile->type == FileType::IMAGE && $profile->show_thumbs) {
                Utils::create_file_thumb($topdir, $destpath);
                //chmod(TODO thumbpath, $filemode);
            }
        } else {
            throw new RuntimeException('Failed to move uploaded file '.$fn);
        }
    };

    if (is_array($f['tmp_name'])) {
        for ($idx = 0, $n = count($f['tmp_name']); $idx < n; ++$idx) {
            $do_file($idx);
        }
    } else {
        $do_file();
    }

    // All good, send a response
    echo json_encode([
        'status' => 'ok',
        'path' => $destpath
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_NUMERIC_CHECK);

} catch (Exception $e) {
    // Something went wrong, send an err response
    http_response_code(400);

    echo json_encode([
        'status' => 'error',
        'message' => trim($e->getMessage())
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_NUMERIC_CHECK);
    // mimic outer catcher
    debug_to_log('Exception: ' . $e->GetMessage());
//  if (CMS_DEBUG) {
    debug_to_log($e->GetTraceAsString());
//  }
}
exit;