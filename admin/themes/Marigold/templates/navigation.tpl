{strip}
{if !isset($depth)}{$depth = 0}{/if}
{if $depth == 0}
<nav class="navigation" id="pg_menu" role="navigation">
<ul id="pg_pagemenu">
{/if}
{foreach $nav as $navitem}
    <li class="nav{if !isset($navitem.system) && (isset($navitem.module) || isset($navitem.firstmodule))} module{/if}{if !empty($navitem.selected) || (isset($smarty.get.section) && $smarty.get.section == $navitem.name|lower)} current{/if}">
        <a href="{$navitem.url}" class="{$navitem.name|lower}{if isset($navitem.children)} parent{/if}"{if isset($navitem.target)} target="_blank"{/if} title="{if !empty($navitem.description)}{$navitem.description|strip_tags}{else}{$navitem.title|strip_tags}{/if}" {if substr($navitem.url,0,6) == 'logout' and isset($is_sitedown)}onclick="return confirm('{'maintenance_warning'|lang|escape:'javascript'}')"{/if}>
        {if $depth > 0}{$navitem.title}{/if}
        </a>
        {if $depth == 0}
            <span class="open-nav" title="{lang('open')}/{lang('close')} {$navitem.title|strip_tags}">{$navitem.title}</span>
        {/if}
        {if isset($navitem.children)}
        {if $depth == 0}<ul>{/if}
            {include file=$smarty.template nav=$navitem.children depth=$depth+1}
        {if $depth == 0}</ul>{/if}
        {/if}
    </li>
{/foreach}
{if $depth == 0}
    </ul>
</nav>
{/if}
{/strip}
