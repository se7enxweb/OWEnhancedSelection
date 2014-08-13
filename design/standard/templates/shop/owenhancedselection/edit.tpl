{def $class_content=$attribute.class_content}
<select name="{$select_name}" {if $class_content.is_multiselect}multiple="multiple" style="width: 100%; height: 300px;"{/if}>
    {foreach $class_content.available_options as $option}
        {if $option.type|eq('optgroup')}
            <optgroup label="{$option.name|wash}">
                {foreach $option.option_list as $sub_option}
                    <option value="{$sub_option.name|wash}" {if eq( $sub_option.name, $current_val )}selected="selected"{/if}>
                        {$sub_option.name|wash}
                    </option>
                {/foreach}
            </optgroup>
        {else}
            <option value="{$option.name|wash}" {if eq( $option.name, $current_val )}selected="selected"{/if}>
                {$option.name|wash}
            </option>
        {/if}
    {/foreach}
</select>
{undef}