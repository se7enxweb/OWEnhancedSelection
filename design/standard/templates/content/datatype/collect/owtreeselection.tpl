{let content=$attribute.content
     classContent=$attribute.class_content
     available_options=$classContent.options
     id=$attribute.id}

{section show=and(is_set($classContent.db_options),count($classContent.db_options)|gt(0))}
    {set available_options=$classContent.db_options}
{/section}

<select name="ContentObjectAttribute_owtreeselection_selection_{$id}[]"
        {section show=$classContent.is_multiselect}multiple="multiple"{/section}>
        
    {section var=option loop=$available_options}
        <option value="{$option.item.identifier|wash}"
                {section show=$content|contains($option.item.identifier)}selected="selected"{/section}>
            {$option.item.name|wash}
        </option>
    {/section}      
        
</select>  

{/let}