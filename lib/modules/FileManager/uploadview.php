<?php
#FileManager module action: defaultadmin - included file for uploads setup
#Copyright (C) 2006-2018 Morten Poulsen <morten@poulsen.org>
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

use FileManager\filemanager_utils;

// UPSTREAM
//if (!isset($gCms)) exit;
//if (!$this->CheckPermission('Modify Files')) exit;

$smarty->assign('formstart', $this->CreateFormStart($id, 'upload', $returnid, 'post',
 'multipart/form-data', false, '', [
  'disable_buffer'=>'1',
  'path'=>$path,
  ]));
//$smarty->assign('formend', $this->CreateFormEnd());
//$smarty->assign('actionid', $id);
//$smarty->assign('maxfilesize', $config['max_upload_size']);

$action_url = str_replace('&amp;', '&', $this->create_url($id, 'upload', $returnid));
$refresh_url = str_replace('&amp;', '&', $this->create_url($id, 'admin_fileview', '', ['ajax'=>1,'path'=>$path])).'&cmsjobtype=1';

$post_max_size = filemanager_utils::str_to_bytes(ini_get('post_max_size'));
$upload_max_filesize = filemanager_utils::str_to_bytes(ini_get('upload_max_filesize'));
$max_chunksize = min($upload_max_filesize, $post_max_size - 1024);
if (isset($_SERVER['HTTP_USER_AGENT']) && (strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE') !== false)) {
    $smarty->assign('is_ie', 1);
}
$smarty->assign('ie_upload_message', $this->Lang('ie_upload_message'));

$css = <<<EOS
<style type="text/css">
/*.upload-wrapper {
 margin: 10px 0
} */
.hcentered {
 text-align: center
}
.vcentered {
 display: table-cell;
 vertical-align: middle
}
#dropzone {
 margin: 15px 0;
 border-radius: 4px;
 border: 2px dashed #ccc
}
#dropzone:hover {
 cursor: move
}
#progressarea {
 margin: 15px;
 height: 2em;
 line-height: 2em;
 text-align: center;
 border: 1px solid #aaa;
 border-radius: 4px;
 display: none
}
</style>
EOS;
$this->AdminHeaderContent($css);

$js = <<<EOS
<script type="text/javascript">
//<![CDATA[
function barValue(total, str) {
  $("#progressarea").progressbar({
    value: parseInt(total)
  });
  $(".ui-progressbar-value").html(str);
}

$(document).ready(function() {
  var _jqXHR = []; // jqXHR array
  var _files = []; // filenames
  // prevent browser default drag/drop handling
  $(document).on('drop dragover', function(e) {
    // prevent default drag/drop stuff.
    e.preventDefault();
  });
  $('#cancel').on('click', function(e) {
    e.preventDefault();
//    aborting = true; //CHECKME
    var ul = $('#fileupload').data('fileupload');
    if(typeof ul !== 'undefined') {
      var data = {};
      data.errorThrown = 'abort';
      ul._trigger('fail', e, data);
    }
  });
  // create our file upload area.
  $('#fileupload').fileupload({
    add: function(e, data) {
      _files.push(data.files[0].name);
      _jqXHR.push(data.submit());
    },
    dataType: 'json',
    dropZone: $('#dropzone'),
    maxChunkSize: $max_chunksize,
    start: function(e, data) {
      $('#cancel').show();
      $('#progressarea').show();
    },
    done: function(e, data) {
      _files = [];
      _jqXHR = [];
    },
    fail: function(e, data) {
      $.each(_jqXHR, function(index, obj) {
        if(typeof obj === 'object') {
          obj.abort();
          if(index < _files.length && typeof data.url !== 'undefined') {
            // now delete the file
            var turl = '{$action_url}&' + $.param({ file: _files[index] });
            $.ajax({
              url: turl,
              type: 'DELETE'
            });
          }
        }
      });
      _jqXHR = [];
      _files = [];
    },
    progressall: function(e, data) {
      // overall progress callback
      var perc = (data.loaded / data.total * 100).toFixed(2);
      var total = null;
      total = (data.loaded / data.total * 100).toFixed(0);
      var str = perc + ' %';
      //console.log(total);
      barValue(total, str);
    },
    stop: function(e, data) {
	  $('#filesarea').load('$refresh_url');
      $('#cancel').fadeOut();
      $('#progressarea').fadeOut();
    }
  });
});
//]]>
</script>
EOS;
$this->AdminBottomContent($js);

echo $this->ProcessTemplate('uploadview.tpl');
