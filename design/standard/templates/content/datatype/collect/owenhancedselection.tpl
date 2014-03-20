{let content=$attribute.content
     $class_content=$attribute.class_content}

<select name="ContentObjectAttribute_owenhancedselection_selection_{$attribute.id}[]"
        {if $class_content.is_multiselect}multiple="multiple"{/if}>

    {foreach $class_content.available_options as $option}
        {if $option.type|eq('optgroup')}
            <optgroup label="{$option.name|wash}">
                {foreach $option.option_list as $sub_option}
                    <option value="{$sub_option.identifier|wash}"
                            {if $content.identifiers|contains($sub_option.identifier)}selected="selected"{/if}>
                        {$sub_option.name|wash}
                    </option>
                {/foreach}
            </optgroup>
        {else}
            <option value="{$option.identifier|wash}"
                    {if $content.identifiers|contains($option.identifier)}selected="selected"{/if}>
                {$option.name|wash}
            </option>
        {/if}
    {/foreach}    

</select>  

{/let}