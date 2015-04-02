{def $content=$attribute.content
     $class_content=$attribute.class_content}

{if ezhttp_hasvariable( concat('ContentObjectAttribute_owenhancedselection_selection_',$attribute.id) )}
    {def $post_value = ezhttp( concat('ContentObjectAttribute_owenhancedselection_selection_',$attribute.id) )
         $selected_id_array = array(cond(is_null($post_value)|not(), $post_value , $content.identifiers))}
{elseif is_set($value)}
    {def $selected_id_array = array($value)}
{else}
    {def $selected_id_array = array()}
{/if}


<select name="ContentObjectAttribute_owenhancedselection_selection_{$attribute.id}[]"
        {if $class_content.is_multiselect}multiple="multiple"{/if} {if is_set($html_class)}class="{$html_class}"{/if} {if is_set($html_id)}id="{$html_id}"{/if}>
    {if $attribute.is_required|not()}
        <option value=""></option>
    {/if}
    {foreach $class_content.options as $option}
        {if $option.type|eq('optgroup')}
            <optgroup label="{$option.name|wash}">
                {foreach $option.option_list as $sub_option}
                    <option value="{$sub_option.identifier|wash}"
                            {if $selected_id_array|contains($sub_option.identifier)}selected="selected"{/if}>
                        {$sub_option.name|wash}
                    </option>
                {/foreach}
            </optgroup>
        {else}
            <option value="{$option.identifier|wash}"
                    {if $selected_id_array|contains($option.identifier)}selected="selected"{/if}>
                {$option.name|wash}
            </option>
        {/if}
    {/foreach}    

</select>  

{undef}
