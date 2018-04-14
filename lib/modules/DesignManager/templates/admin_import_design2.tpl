<script type="text/javascript">
{literal}//<![CDATA[
$(document).ready(function() {
  $('.template_view').on('click', function() {
    var row = $(this).closest('tr');
    cms_dialog($('.template_content',row), {
      width: 'auto',
      close: function(ev, ui) {
        cms_dialog($(this), 'destroy');
      }
    });
    return false;
  });
  $('.stylesheet_view').on('click', function() {
    var row = $(this).closest('tr');
    cms_dialog($('.stylesheet_content',row), {
      width: 'auto',
      close: function(ev, ui) {
        cms_dialog($(this), 'destroy');
      }
    });
    return false;
  });
});
{/literal}//]]>
</script>

<h3>{$mod->Lang('import_design_step2')}</h3>

{form_start step=2 tmpfile=$tmpfile}
<div class="pageinfo">{$mod->Lang('info_import_xml_step2')}</div>

<fieldset>
  <!-- TODO GRID -->
  <div style="width:49%;float:left;">
    <div class="pageoverflow">
      <p class="pagetext">
      {$lbltxt=$mod->Lang('prompt_name')}<label for="import_newname">{$lbltxt}:</label>
      {cms_help realm=$_module key2='help_import_newname' title=$lbltxt}
      </p>
      <p class="pageinput">
        <input id="import_newname" type="text" name="{$actionid}newname" value="{$new_name}" size="50" maxlength="50" />
        <br/>
        {$mod->Lang('prompt_orig_name')}: {$design_info.name}
      </p>
    </div>

    <div class="pageoverflow">
      <p class="pagetext">
      {$lbltxt=$mod->Lang('prompt_created')}{$lbltext}:
      {cms_help realm=$_module key2='help_import_created' title=$lbltext}
      </p>
      <p class="pageinput">
        {$tmp=$design_info.generated|date_format:'%x %X'}{if $tmp == ''}{$tmp=$mod->Lang('unknown')}{/if}
        <span class="red">{$tmp}</span>
      </p>
    </div>
  </div>

  <div style="width:49%;float:right;">
    <div class="pageoverflow">
      <p class="pagetext">
      {$lbltxt=$mod->Lang('prompt_cmsversion')}{$lbltext}:
      {cms_help realm=$_module key2='help_import_cmsversion' title=$lbltext}
      </p>
      <p class="pageinput">
        {if version_compare($design_info.cmsversion,$cms_version) < 0}
          <span class="red">{$design_info.cmsversion}</span>
        {else}
          {$design_info.cmsversion}
        {/if}
      </p>
    </div>
  </div>
</fieldset>

{tab_header name='description' label=$mod->Lang('prompt_description')}
{* tab_header name='copyright' label=$mod->Lang('prompt_copyrightlicense') *}
{tab_header name='templates' label=$mod->Lang('prompt_templates')}
{tab_header name='stylesheets' label=$mod->Lang('prompt_stylesheets')}

{tab_start name='description'}

<textarea name={$actionid}newdescription rows="5" cols="80">{$design_info.description}</textarea>

{* tab_start name='copyright' *}

{tab_start name='templates'}
<table class="pagetable">
  <thead>
    <tr>
      <th>{$mod->Lang('name')}</th>
      <th>{$mod->Lang('newname')}</th>
      <th>{$mod->Lang('type')}</th>
      <th>{$mod->Lang('prompt_description')}</th>
      <th class="pageicon"></th>
    </tr>
  </thead>
  <tbody>
  {foreach $templates as $one}
   {$typename=$one.type_originator|cat:'::'|cat:$one.type_name}
   {$type_obj=CmsLayoutTemplateType::load($typename)}
   <tr class="{cycle values='row1,row2'}">
    <td>
      <span data-idx="{$one@index}" class="template_view pointer">{$one.name}</span>
    </td>
    <td><h3>{$one.newname}</h3></td>
    <td>{$type_obj->get_langified_display_value()}</td>
    <td>{$one.desc|default:$mod->Lang('info_nodescription')|summarize:80}
      <div id="tpl_{$one@index}" class="template_content" title="{$one.name}" style="display:none;"><textarea rows="10" cols="80">{$one.data}</textarea></div>
    </td>
    <td>
      {admin_icon class="template_view pointer" icon='view.gif' alt=lang('view')}
    </td>
  </tr>
  {/foreach}
  </tbody>
</table>

{tab_start name='stylesheets'}
<div id="stylesheet_list">
  <table class="pagetable">
    <thead>
      <tr>
        <th>{$mod->Lang('name')}</th>
        <th>{$mod->Lang('newname')}</th>
	<th>{$mod->Lang('prompt_media_type')}</th>
        <th>{$mod->Lang('prompt_description')}</th>
	<th class="pageicon"></th>
      </tr>
    </thead>
    <tbody>
      {foreach $stylesheets as $one}
      <tr>
        <td>{$one.name}</td>
	<td>
	  <h3>{$one.newname}</h3>
	</td>
	<td>{$one.mediatype}</td>
        <td>{$one.desc|default:$mod->Lang('info_nodescription')}
           <div class="stylesheet_content" title="{$one.name}" style="display: none;">
	     <textarea rows="10" cols="80">{$one.data}</textarea>
	   </div>
	</td>
	<td>
          {admin_icon class="stylesheet_view pointer" icon='view.gif' alt=lang('view')}
	</td>
      </tr>
      {/foreach}
    </tbody>
  </table>
</div>
{tab_end}

<div class="pageoverflow">
  <p class="pagetext">*{$mod->Lang('confirm_import')}:</p>
  <p class="pageinput">
    <input type="checkbox" name="{$actionid}check1" value="1" id="check1">&nbsp;<label for="check1">{$mod->Lang('confirm_import_1')}</label>
  </p>
</div>
<div class="pageinput pregap">
  <button type="submit" name="{$actionid}next2" class="adminsubmit icon go">{$mod->Lang('next')}</button>
  <button type="submit" name="{$actionid}cancel" class="adminsubmit icon cancel">{$mod->Lang('cancel')}</button>
</div>
</form>
