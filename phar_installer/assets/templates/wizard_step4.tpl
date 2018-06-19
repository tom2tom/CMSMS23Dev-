{* wizard step 4 *}

{extends file='wizard_step.tpl'}
{block name='logic'}
    {$subtitle = 'title_step4'|tr}
    {$current_step = '4'}
{/block}

{block name='contents'}

<div class="installer-form">
{wizard_form_start}

    <h3>{'prompt_dbinfo'|tr}</h3>
    <p class="info">{'info_dbinfo'|tr}</p>

    <fieldset>
        <div class="row form-row">
            <div class="four-col">
                <label>{'prompt_dbhost'|tr}</label>
            </div>
            <div class="eight-col">
                <input class="form-field required full-width" type="text" name="db_hostname" value="{$config.db_hostname}" required="required"/>
                <div class="corner red">
                    <i class="icon-asterisk"></i>
                </div>
            </div>
        </div>
        <div class="row form-row">
            <div class="four-col">
                <label>{'prompt_dbname'|tr}</label>
            </div>
            <div class="eight-col">
                <input class="form-field required full-width" type="text" name="db_name" value="{$config.db_name}" required="required"/>
                <div class="corner red">
                    <i class="icon-asterisk"></i>
                </div>
            </div>
        </div>
        <div class="row form-row">
            <div class="four-col">
                <label>{'prompt_dbuser'|tr}</label>
            </div>
            <div class="eight-col">
                <input class="form-field required full-width" type="text" name="db_username" value="{$config.db_username}" required="required" autocomplete="off"/>
                <div class="corner red">
                    <i class="icon-asterisk"></i>
                </div>
            </div>
        </div>
        <div class="row form-row">
            <div class="four-col">
                <label>{'prompt_dbpass'|tr}</label>
            </div>
            <div class="eight-col">
                <input class="form-field required full-width" type="password" name="db_password" value="" autocomplete="false" required="required"/>
                <div class="corner red">
                    <i class="icon-asterisk"></i>
                </div>
            </div>
        </div>
        {if $verbose}
        <div class="row form-row">
            <div class="four-col">
                <label>{'prompt_dbport'|tr}</label>
            </div>
            <div class="eight-col">
                <input class="form-field full-width" type="text" name="db_port" value="{$config.db_port}"/>
            </div>
        </div>
        <div class="row form-row">
            <div class="four-col">
                <label>{'prompt_dbprefix'|tr}</label>
            </div>
            <div class="eight-col">
                <input class="form-field full-width" type="text" name="db_prefix" value="{$config.db_prefix}"/>
            </div>
        </div>
        {/if}
    </fieldset>

    <h3>{'prompt_timezone'|tr}</h3>
    <p class="info">{'info_timezone'|tr}</p>

    <div class="row form-row">
        <label class="visuallyhidden">{'prompt_timezone'|tr}</label>
        <select class="form-field" name="timezone">
            {html_options options=$timezones selected=$config.timezone}
        </select>
    </div>

    {if $verbose}
    <h3>{'prompt_queryvar'|tr}</h3>
    <p class="info">{'info_queryvar'|tr}</p>

    <div class="row form-row">
        <div class="four-col">
            <label>{'prompt_queryvar'|tr}</label>
        </div>
        <div class="eight-col">
            <input class="form-field" type="text" name="query_var" value="{$config.query_var}" />
        </div>
    </div>
    {/if}

    {if $verbose and $action == 'install'}
    <h3>{'prompt_installcontent'|tr}</h3>
    <p class="info">{'info_installcontent'|tr}</p>

    <div class="row form-row">
        <label>{'prompt_installcontent'|tr}</label>
        <select class="form-field" name="samplecontent">
            {html_options options=$yesno selected=$config.samplecontent}
        </select>
    </div>
    {/if}

    <div id="bottom_nav">
    <input class="action-button positive" type="submit" name="next" value="{'next'|tr} &rarr;" />
    </div>

{wizard_form_end}
</div>
{/block}