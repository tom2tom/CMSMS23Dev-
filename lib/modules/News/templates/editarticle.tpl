<h3>{if isset($articleid)}{$mod->Lang('editarticle')}{else}{$mod->Lang('addarticle')}{/if}</h3>
{strip}
<div id="edit_news">
  {$startform}
  {$hidden|default:''}
  <div class="pageoptions">
    <p class="pageinput">
      <button type="submit" name="{$actionid}submit" class="adminsubmit icon check">{$mod->Lang('submit')}</button>
      <button type="submit" name="{$actionid}cancel" id="{$actionid}cancel" class="adminsubmit icon cancel">{$mod->Lang('cancel')}</button>
      {if isset($articleid)}
      <button type="submit" name="{$actionid}apply" class="adminsubmit icon apply">{$mod->Lang('apply')}</button>
      {/if}
    </p>
  </div>

  {if isset($start_tab_headers)}
  {$start_tab_headers}
  {$tabheader_article}
  {$tabheader_preview}
  {$end_tab_headers}

  {$start_tab_content}
  {$start_tab_article}
  {/if}
  <div id="edit_article">
    {if $inputauthor}
    <div class="pageoverflow">
      <p class="pagetext">*{$authortext}:</p>
      <p class="pageinput">
        {$inputauthor}
      </p>
    </div>
    {/if}

    <div class="pageoverflow">
      <p class="pagetext">
        <label for="fld1">*{$titletext}:</label> {cms_help realm=$_module key='help_article_title' title=$titletext}
      </p>
      <p class="pageinput">
        <input type="text" name="{$actionid}title" id="fld1" value="{$title}" size="80" maxlength="255" required />
      </p>
    </div>

    <div class="pageoverflow">
      <p class="pagetext">
        <label for="fld2">*{$categorytext}:</label> {cms_help realm=$_module key='help_article_category' title=$categorytext}
      </p>
      <p class="pageinput">
        <select name="{$actionid}category" id="fld2">
         {html_options options=$categorylist selected=$category}
        </select>
      </p>
    </div>
    {if !isset($hide_summary_field) || $hide_summary_field == '0'}
    <div class="pageoverflow">
      <p class="pagetext">
        {$summarytext}: {cms_help realm=$_module key='help_article_summary' title=$summarytext}
      </p>
      <p class="pageinput">
        {$inputsummary}
      </p>
    </div>
    {/if}

    <div class="pageoverflow">
      <p class="pagetext">
        *{$contenttext}: {cms_help realm=$_module key='help_article_content' title=$contenttext}
      </p>
      <p class="pageinput">
        {$inputcontent}
      </p>
    </div>
    {if isset($statustext)}
    <div class="pageoverflow">
      <p class="pagetext">
        <label for="fld9">*{$statustext}:</label> {cms_help realm=$_module key='help_article_status' title=$statustext}
      </p>
      <p class="pageinput">
        <select name="{$actionid}status" id="fld9">
         {html_options options=$statuses selected=$status}
        </select>
      </p>
    </div>
    {else}
    <input type="hidden" name="{$actionid}status" value="{$status}" />
    {/if}

    <div class="pageoverflow">
      <p class="pagetext">
        <label for="fld7">{$urltext}:</label> {cms_help realm=$_module key='help_article_url' title=$urltext}
      </p>
      <p class="pageinput">
        <input type="text" name="{$actionid}news_url" id="fld7" value="{$news_url}" size="50" maxlength="255" />
      </p>
    </div>

    <div class="pageoverflow">
      <p class="pagetext">
        <label for="fld5">{$extratext}:</label> {cms_help realm=$_module key='help_article_extra' title=$extratext}
      </p>
      <p class="pageinput">
        <input type="text" name="{$actionid}extra" id="fld5" value="{$extra}" size="50" maxlength="255" />
      </p>
    </div>

    <div class="pageoverflow">
      <p class="pagetext">
        {$postdatetext}: {cms_help realm=$_module key='help_article_postdate' title=$postdatetext}
      </p>
      <p class="pageinput">
        {html_select_date prefix=$postdateprefix time=$postdate start_year='1980' end_year='+15'}
        {html_select_time prefix=$postdateprefix time=$postdate}
      </p>
    </div>

    <div class="pageoverflow">
      <p class="pagetext">
        <label for="searchable">{$mod->Lang('searchable')}:</label>
        {cms_help realm=$_module key='help_article_searchable' title=$mod->Lang('searchable')}
      </p>
      <input type="hidden" name="{$actionid}searchable" value="0" />
      <p class="pageinput">
        <input type="checkbox" name="{$actionid}searchable" id="searchable" value="1"{if $searchable} checked="checked"{/if} />
        <br />
        {$mod->Lang('info_searchable')}
      </p>
    </div>

    <div class="pageoverflow">
      <p class="pagetext">
        <label for="fld11">{$useexpirationtext}:</label> {cms_help realm=$_module key='help_article_useexpiry' title=$useexpirationtext}
      </p>
      <p class="pageinput">
        <input type="checkbox" name="{$actionid}useexp" id="fld11"{if $useexp==1} checked="checked"{/if} class="pagecheckbox" />
      </p>
    </div>

    <div id="expiryinfo"{if $useexp !=1 } style="display: none;"{/if}>
      <div class="pageoverflow">
        <p class="pagetext">
          {$startdatetext}: {cms_help realm=$_module key='help_article_startdate' title=$startdatetext}
        </p>
        <p class="pageinput">
          {html_select_date prefix=$startdateprefix time=$startdate start_year="-10" end_year="+15"}
          {html_select_time prefix=$startdateprefix time=$startdate}
        </p>
      </div>
      <div class="pageoverflow">
        <p class="pagetext">
          {$enddatetext}: {cms_help realm=$_module key='help_article_enddate' title=$enddatetext}
        </p>
        <p class="pageinput">
          {html_select_date prefix=$enddateprefix time=$enddate start_year="-10" end_year="+15"}
          {html_select_time prefix=$enddateprefix time=$enddate}
        </p>
      </div>
    </div>
    {if isset($custom_fields)}
    {foreach $custom_fields as $field}
    <div class="pageoverflow">
      <p class="pagetext">
        <label for="{$field->idattr}">{$field->prompt}:</label>
      </p>
      <p class="pageinput">
        {if $field->type == 'textbox'}
          <input type="text" name="{$field->nameattr}" id="{$field->idattr}" value="{$field->value}" size="{$field->size}" maxlength="{$field->max_len}" />
        {elseif $field->type == 'checkbox'}
          <input type="hidden" name="{$field->nameattr}" value="0" />
        <input type="checkbox" name="{$field->nameattr}" id="{$field->idattr}" value="1"{if $field->value == 1} checked="checked"{/if} />
        {elseif $field->type == 'textarea'}
          {cms_textarea id=$field->idattr name=$field->nameattr enablewysiwyg=1 value=$field->value maxlength=$field->max_len}
        {elseif $field->type == 'file'}
          {if !empty($field->value)}{$field->value}<br />{/if}
          <input type="file" name="{$field->nameattr}" id="{$field->idattr}" />
          {if !empty($field->value)}{$delete_field_val} <input type="checkbox" name="{$field->delete}" value="delete" />{/if}
        {elseif $field->type == 'dropdown'}
         <select name="{$field->nameattr}" id="{$field->idattr}">
          <option value="-1">{$select_option}</option>
          {html_options options=$field->options selected=$field->value}
         </select>
        {elseif $field->type == 'linkedfile'}
         {if $field->value} {thumbnail_url file=$field->value assign=tmp} {if $tmp}<img src="{$tmp}" alt="{$field->value}" />{/if}{/if}
         {cms_filepicker name="{$field->nameattr}" value=$field->value}
        {/if}
      </p>
    </div>
    {/foreach}
    {/if}
  </div>
  {if isset($end_tab_article)} {$end_tab_article} {/if} {/strip}
  {if isset($start_tab_preview)} {$start_tab_preview} {strip}
  <div class="pagewarn">{$warning_preview}</div>
  <fieldset>
    <label for="preview_template">{$prompt_detail_template}:</label>
    <select name="{$actionid}preview_template" id="preview_template">
      {html_options options=$detail_templates selected=$cur_detail_template}
    </select>&nbsp;
    <label>{$prompt_detail_page}: {$preview_returnid}</label>
  </fieldset>
  <br/>
  <iframe id="previewframe" style="height: 800px; width: 100%; border: 1px solid black; overflow: auto;"></iframe>
  {$end_tab_preview}
  {$end_tab_content}
  {/if}

  <div class="pageoverflow">
    <p class="pageinput">
      <button type="submit" name="{$actionid}submit" class="adminsubmit icon check">&nbsp;{$mod->Lang('submit')}</button>
      <button type="submit" name="{$actionid}cancel" id="{$actionid}cancel" class="adminsubmit icon cancel">{$mod->Lang('cancel')}</button>
      {if isset($articleid)}&nbsp;
      <button type="submit" name="{$actionid}apply" class="adminsubmit icon apply">{$mod->Lang('apply')}</button>
      {/if}
    </p>
  </div>
  {$endform}
</div>
{/strip}
