<h3>{$mod->Lang('delete_template')}</h3>

{if count($tpl->get_designs()) gt 0 || $page_usage gt 0 }
<div class="pagewarn">{$mod->Lang('pagewarn_template_used')}</div>
{/if}

{form_start tpl=$actionparams.tpl}
<fieldset>
  <div style="width:49%;float:left;">
  <div class="pageoverflow">
    <p class="pagetext">
      {$lbltext=$mod->Lang('prompt_name')}<label for="tpl_name">* {$lbltext}:</label>
      {cms_help realm=$_module key2='help_copytemplate_name' title=$lbltext}
    </p>
    <p class="pageinput">
      <input id="tpl_name" type="text" size="50" maxlength="50" value="{$tpl->get_name()}" readonly="readonly" />
    </p>
  </div>

  {if isset($type_list)}
  <div class="pageoverflow">
    <p class="pagetext">{$mod->Lang('prompt_type')}:</p>
    <p class="pageinput">
      {$type_list[$tpl->get_type_id()]}
    </p>
  </div>
  {/if}

  {if isset($category_list)}
  <div class="pageoverflow">
    <p class="pagetext">{$mod->Lang('prompt_category')}:</p>
    <p class="pageinput">
      {$category_list[$tpl->get_category_id()|default:0]}
    </p>
  </div>
  {/if}

  {if isset($design_list)}
  <div class="pageoverflow">
    <p class="pagetext">{$mod->Lang('prompt_designs')}:</p>
    <p class="pageinput">
      {foreach $tpl->get_designs() as $dsn}
        {$design_list[$dsn]}
        {if !$dsn@last}<br />{/if}
      {/foreach}
    </p>
  </div>
  {/if}

  </div>{* column *}

  <div style="width: 49%; float: right;">
  {if $tpl->get_id()}
    <div class="pageoverflow">
      <p class="pagetext">{$mod->Lang('prompt_created')}:</p>
      <p class="pageinput">
        <input type="text" id="tpl_created" value="{$tpl->get_created()|date_format:'%x %X'}" readonly="readonly"/>
      </p>
    </div>
    <div class="pageoverflow">
      <p class="pagetext">
      <label for="tpl_modified">{$mod->Lang('prompt_modified')}:</label>
      </p>
      <p class="pageinput">
        <input type="text" id="tpl_modified" value="{$tpl->get_modified()|date_format:'%x %X'}" readonly="readonly"/>
      </p>
    </div>
  {/if}

  {if isset($user_list)}
  <div class="pageoverflow">
    <p class="pagetext">{$mod->Lang('prompt_owner')}:</p>
    <p class="pageinput">
      {$user_list[$tpl->get_owner_id()]}
    </p>
  </div>
  {/if}

  </div>{* column *}
</fieldset>

<div class="pagewarn">{$mod->Lang('info_template_delete')}</div>
<div class="pageoverflow">
  <div class="pageinput pregap">
    <input id="check1" type="checkbox" name="{$actionid}check1" value="1" />&nbsp;<label for="check1">{$mod->Lang('confirm_delete_template_1')}</label><br/>
    <input id="check2" type="checkbox" name="{$actionid}check2" value="1" />&nbsp;<label for="check2">{$mod->Lang('confirm_delete_template_2')}</label>
  </div>
</div>
<div class="pageinput pregap">
  <button type="submit" name="{$actionid}submit" class="adminsubmit icon check">{$mod->Lang('submit')}</button>
  <button type="submit" name="{$actionid}cancel" class="adminsubmit icon cancel">{$mod->Lang('cancel')}</button>
</div>
</form>
