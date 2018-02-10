<script type="text/javascript">
//<![CDATA[{literal}
$(document).ready(function() {
  $('#showmore_ctl').click(function() {
    $(this).closest('form').submit();
  });
});
//]]>{/literal}
</script>

<h3>{$mod->Lang('prompt_bulk_setdesign')}:</h3>

{form_start multicontent=$multicontent}
<div class="pageoverflow">
  <ul>
   {foreach $displaydata as $rec}
    <li>({$rec.id}) : {$rec.name} <em>({$rec.alias})</em></li>
   {/foreach}
  </ul>
</div>

<div class="warning">{$mod->Lang('warn_destructive')}</div>

<div class="pageoverflow">
  <p class="pagetext"><label for="design_ctl">{$mod->Lang('prompt_design')}:</label></p>
  <p class="pageinput"><select id="design_ctl" name="{$actionid}design">
    {html_options options=$alldesigns selected=$dflt_design_id}
  </select></p>
</div>

<div class="pageoverflow">
  <p class="pagetext"><label for="template_ctl">{$mod->Lang('prompt_template')}:</label></p>
  <p class="pageinput"><select id="template_ctl" name="{$actionid}template">
    {html_options options=$alltemplates selected=$dflt_tpl_id}
  </select></p>
</div>

<div class="pageoverflow">
  <input type="hidden" name="{$actionid}showmore" value="0"/>
  <p class="pageinput">
    <input type="checkbox" id="showmore_ctl" name="{$actionid}showmore" value="1"{if $showmore} checked="checked"{/if}/>
    &sbsp;<label for="showmore_ctl">{$mod->Lang('prompt_showmore')}</label>
   </p>
</div>

<div class="pageoverflow">
  <p class="pagetext">{$mod->Lang('prompt_confirm_operation')}:</p>
  <p class="pageinput">
    <input type="checkbox" id="confirm1" value="1" name="{$actionid}confirm1"/>
    &nbsp;<label for="confirm1">{$mod->Lang('prompt_confirm1')}</label>
    <br/>
    <input type="checkbox" id="confirm2" value="1" name="{$actionid}confirm2"/>
    &nbsp;<label for="confirm2">{$mod->Lang('prompt_confirm2')}</label>
   </p>
</div>

<div class="pageoverflow">
  <p class="pageinput">
    <button type="submit" role="button" name="{$actionid}submit" value="{$mod->Lang('submit')}" class="pagebutton ui-button ui-widget ui-state-default ui-corner-all ui-button-text-icon-primary">
     <span class="ui-button-icon-primary ui-icon ui-icon-circle-check"></span>
     <span class="ui-button-text">{$mod->Lang('submit')}</span>
    </button>
    <button type="submit" role="button" name="{$actionid}cancel" value="{$mod->Lang('cancel')}" class="pagebutton ui-button ui-widget ui-corner-all ui-button-text-icon-primary">
     <span class="ui-button-icon-primary ui-icon ui-icon-circle-close"></span>
     <span class="ui-button-text">{$mod->Lang('cancel')}</span>
    </button>
  </p>
</div>
{form_end}
