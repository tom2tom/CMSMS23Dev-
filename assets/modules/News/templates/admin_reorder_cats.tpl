{function category_tree parent=-1 depth=1}{strip}
<ul{if $depth==1} class="sortableList sortable"{/if}>
{foreach $allcats as $cat}
  {if $cat.parent_id == $parent}
  <li id="cat_{$cat.news_category_id}">
    <div class="label">{$cat.news_category_name}</div>
    {category_tree parent=$cat.news_category_id depth=$depth+1}
  </li>
  {/if}
{/foreach}
</ul>
{/strip}{/function}

<h3>{$mod->Lang('reorder_categories')}</h3>
<div class="pageinfo">{$mod->Lang('info_reorder_categories')}</div>
{category_tree}
<br />
{form_start id="reorder_form"}
<input type="hidden" name="{$actionid}submit_type" id="submit_type" value=""/>
<input type="hidden" name="{$actionid}data" id="submit_data" value=""/>
<div class="pageoverflow">
  <p class="pageinput">
    <button type="submit" name="{$actionid}submit" class="adminsubmit icon check">{$mod->Lang('submit')}</button>
    <button type="submit" name="{$actionid}cancel" class="adminsubmit icon cancel">{$mod->Lang('cancel')}</button>
  </p>
</div>
</form>