<div class="c_full cf">
  <a href="{cms_action_url action=download}&cmsjobtype=1">{admin_icon icon='export.gif'} {$mod->Lang('download')}</a>
  {if $mod->CheckPermission('Clear Admin Log')}
    <a href="{cms_action_url action=clear}">{admin_icon icon='delete.gif'} {$mod->Lang('clearlog')}</a>
  {/if}
  <a id="filterbtn" href="javascript:void()">{admin_icon icon='run.gif'} {$mod->Lang('filter')} ...</a>
  {if count($pagelist) > 1}
    <div style="text-align: right; float: right;">
      {$mod->Lang('page')}:
      <select id="pagenum">
        {html_options options=$pagelist selected=$page}
      </select>
    </div>
  {/if}
</div>

<div id="filter_dlg" title="{$mod->Lang('filter')}" style="display:none;min-width:35em;">
  {form_start}
  <div class="vbox">
    <div class="hbox flow">
      <label class="boxchild" for="f_sev">{$mod->Lang('f_sev')}:</label>
      <select class="boxchild" id="f_sev" name="{$actionid}f_sev">
      {html_options options=$severity_list selected=$filter->severity}
      </select>
    </div>
    <div class="hbox flow">
      <label class="boxchild" for="f_act">{$mod->Lang('f_msg')}:</label>
      <input class="boxchild" id="f_act" name="{$actionid}f_msg" value="{$filter->msg}" />
    </div>
    <div class="hbox flow">
      <label class="boxchild" for="f_item">{$mod->Lang('f_subj')}:</label>
      <input class="boxchild" id="f_item" name="{$actionid}f_subj" value="{$filter->subject}" />
    </div>
    <div class="hbox flow">
      <label class="boxchild" for="f_user">{$mod->Lang('f_user')}:</label>
      <input class="boxchild" id="f_user" name="{$actionid}f_user" value="{$filter->username}" />
    </div>
  </div>
  <div class="pregap">
    <button type="submit" name="{$actionid}filter" class="adminsubmit icon do">{$mod->Lang('filter')}</button>
    <button type="submit" name="{$actionid}reset" class="adminsubmit icon undo">{$mod->Lang('reset')}</button>
  </div>
  </form>
</div>

{if !empty($results)}
<table class="pagetable">
  <thead>
    <tr>
      <th>{$mod->Lang('severity')}</th>
      <th>{$mod->Lang('when')}</th>
      <th>{$mod->Lang('subject')}</th>
      <th>{$mod->Lang('msg')}</th>
      <th>{$mod->Lang('itemid')}</th>
      <th>{$mod->Lang('ip_addr')}</th>
      <th>{$mod->Lang('username')}</th>
    </tr>
  </thead>
  <tbody>
    {foreach $results as $one}
     {if $one.severity == 1}
      {$rowclass='adminlog_notice'}
     {elseif $one.severity == 2}
      {$rowclass='adminlog_warning'}
     {elseif $one.severity == 3}
      {$rowclass='adminlog_error'}
     {else}
      {$rowclass=''}
     {/if}
    <tr class="{cycle values='row1,row2'}">
      <td>{$severity_list[$one.severity]}</td>
      <td>{$one.timestamp|date_format:'%e %h. %Y %H:%M:%S'}</td>
      <td>{$one.subject}</td>
      <td>{$one.msg}</td>
      <td>{if $one.item_id != -1}{$one.item_id}{/if}</td>
      <td>{$one.ip_addr|default:''}</td>
      <td>{$one.username}</td>
    </tr>
    {/foreach}
  </tbody>
</table>
{/if}
