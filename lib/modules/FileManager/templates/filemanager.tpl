{if !isset($ajax)}

{function filebtn}
{strip}{if isset($text) && $text}
  {if !empty($icon)}{$addclass=' icon '|cat:$icon}{else}{$addclass=''}{/if}
  {if !isset($title) || $title == ''}{$title=$text}{/if}
{/if}{/strip}
  <button type="submit" name="{$iname}" id="{$id}" title="{$title|default:''}" class="filebtn adminsubmit{$addclass}">{$text}</button>
{/function}

<div id="popup" style="display: none;">
  <div id="popup_contents" style="min-width: 500px; max-height: 600px;"></div>
</div>
<!-- TODO custom icons for buttons newdir view rename delete move copy unpack thumbnail size rotate -->
<div>
  {$formstart}
  {$hiddenpath}
  <div class="postgap">
      {filebtn id='btn_newdir' iname="{$actionid}fileactionnewdir" icon='plus' text=$mod->Lang('newdir') title=$mod->Lang('title_newdir')}
      {filebtn id='btn_view' iname="{$actionid}fileactionview" icon='' text=$mod->Lang('view') title=$mod->Lang('title_view')}
      {filebtn id='btn_rename' iname="{$actionid}fileactionrename" icon='' text=$mod->Lang('rename') title=$mod->Lang('title_rename')}
      {filebtn id='btn_delete' iname="{$actionid}fileactiondelete" icon='delete' text=$mod->Lang('delete') title=$mod->Lang('title_delete')}
      {filebtn id='btn_move' iname="{$actionid}fileactionmove" icon='' text=$mod->Lang('move') title=$mod->Lang('title_move')}
      {filebtn id='btn_copy' iname="{$actionid}fileactioncopy" icon='' text=$mod->Lang('copy') title=$mod->Lang('title_copy')}
      {filebtn id='btn_unpack' iname="{$actionid}fileactionunpack" icon='' text=$mod->Lang('unpack') title=$mod->Lang('title_unpack')}
      {filebtn id='btn_thumb' iname="{$actionid}fileactionthumb" icon='' text=$mod->Lang('thumbnail') title=$mod->Lang('title_thumbnail')}
      {filebtn id='btn_resizecrop' iname="{$actionid}fileactionresizecrop" icon='' text=$mod->Lang('resizecrop') title=$mod->Lang('title_resizecrop')}
      {filebtn id='btn_rotate' iname="{$actionid}fileactionrotate" icon='' text=$mod->Lang('rotate') title=$mod->Lang('title_rotate')}
  </div>

  <div id="filesarea">
{/if} {* !isset($ajax) *}
    <table style="width:100%;" class="pagetable scrollable">
      <thead>
        <tr>
          <th class="pageicon">&nbsp;</th>
          <th>{$filenametext}</th>
          <th class="pageicon">{$mod->Lang('mimetype')}</th>
          <th class="pageicon">{$fileinfotext}</th>
          <th class="pageicon" title="{$mod->Lang('title_col_fileowner')}">{$fileownertext}</th>
          <th class="pageicon" title="{$mod->Lang('title_col_fileperms')}">{$filepermstext}</th>
          <th class="pageicon" title="{$mod->Lang('title_col_filesize')}" style="text-align:right;">{$filesizetext}</th>
          <th class="pageicon"></th>
          <th class="pageicon" title="{$mod->Lang('title_col_filedate')}">{$filedatetext}</th>
          <th class="pageicon">
            <input type="checkbox" name="tagall" value="tagall" id="tagall" title="{$mod->Lang('title_tagall')}" />
          </th>
        </tr>
      </thead>
      <tbody>
        {foreach $files as $file} {$thedate=str_replace(' ','&nbsp;',$file->filedate|cms_date_format)}{$thedate=str_replace('-','&minus;',$thedate)}
        <tr class="{cycle values='row1,row2'}">
          <td style="vertical-align:middle;">{if isset($file->thumbnail) && $file->thumbnail}{$file->thumbnail}{else}{$file->iconlink}{/if}</td>
          <td class="clickable" style="vertical-align:middle;">{$file->txtlink}</td>
          <td class="clickable" style="vertical-align:middle;">{$file->mime}</td>
          <td class="clickable" style="vertical-align:middle;padding-right:8px;white-space:pre;">{$file->fileinfo}</td>
          <td class="clickable" style="vertical-align:middle;padding-right:8px;white-space:pre;">{if isset($file->fileowner)}{$file->fileowner}{else}&nbsp;{/if}</td>
          <td class="clickable" style="vertical-align:middle;padding-right:8px;">{$file->filepermissions}</td>
          <td class="clickable" style="vertical-align:middle;padding-right:8px;white-space:pre;text-align:right;">{$file->filesize}</td>
          <td class="clickable" style="vertical-align:middle;padding-right:8px;">{if isset($file->filesizeunit)}{$file->filesizeunit}{else}&nbsp;{/if}</td>
          <td class="clickable" style="vertical-align:middle;padding-right:8px;white-space:pre;">{$thedate}</td>
          <td>{if !isset($file->noCheckbox)}
            <label for="x_{$file->urlname}" style="display: none;">{$mod->Lang('toggle')}</label>
            <input type="checkbox" name="{$actionid}selall[]" id="x_{$file->urlname}" value="{$file->urlname}" title="{$mod->Lang('toggle')}" class="fileselect {implode(' ',$file->type)}"{if isset($file->checked)} checked="checked"{/if} />
          {/if}</td>
        </tr>
        {/foreach}
      </tbody>
      <tfoot>
        <tr>
          <td>&nbsp;</td>
          <td colspan="7">{$countstext}</td>
        </tr>
      </tfoot>
    </table>
{if !isset($ajax)}
  </div>
  {*{$actiondropdown}{$targetdir}{$okinput}*}
  {$formend}
</div>
{/if}
