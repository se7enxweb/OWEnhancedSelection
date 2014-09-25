{def $id=$class_attribute.id
     $content=$class_attribute.content
	 $up_enabled = false()
	 $down_enabled = false()
	 $up_image = "button-move_up-disabled.gif"
	 $down_image = "button-move_down-disabled.gif"
     $row_count = 0
     $bg_colors = array('bglight', 'bgdark')}

<div class="block">   
    <fieldset>
        <legend>{"Option list"|i18n('design/standard/class/datatype')}</legend>

        {if count($content.basic_options)|gt(0)}
            <table class="list" cellspacing="0">
                <tr>
                    <th style="width: 1%;" colspan="4">&nbsp;</th>
                    <th>{"Name"|i18n('design/standard/class/datatype')}</th>
                    <th>{"Identifier"|i18n('design/standard/class/datatype')} *</th>
                    <th style="width: 1%;">&nbsp;</th>
                </tr>
                {foreach $content.basic_options as $option_index => $option_item}
                    {set $row_count = $row_count|inc()}
                    <tr class="{$bg_colors[$row_count|mod(2)]}">
                        <td {if $option_item.type|eq('optgroup')}style="border-bottom: 1px solid black"{/if}>
                            {$option_index|inc()}.
                        </td>
                        <td {if $option_item.type|eq('optgroup')}style="border-bottom: 1px solid black"{/if}>
                            <input type="checkbox"
                                   name="ContentClass_owenhancedselection_remove_{$id}[]"
                                   value="{$option_item.id}" />
                        </td>
                        <td colspan="2"></td>
                        <td>
                            <input type="hidden"
                                   name="ContentClass_owenhancedselection_id_{$id}[]"
                                   value="{$option_item.id}" />
                            <input type="text"
                                   name="ContentClass_owenhancedselection_name_{$id}[{$option_item.id}]"
                                   value="{$option_item.name|wash}" />
                        </td>
                        <td>
                            <input type="text"
                                   name="ContentClass_owenhancedselection_identifier_{$id}[{$option_item.id}]"
                                   value="{$option_item.identifier|wash}" />
                        </td>
                        <td>
                            <div style="white-space: nowrap;">
                                {set $up_enabled=$option_index|ne(0)
                                     $down_enabled=$option_index|lt(count($content.basic_options)|dec())
                                     $up_image=cond($up_enabled,"button-move_up.gif","button-move_up-disabled.gif")
                                     $down_image=cond($down_enabled,"button-move_down.gif","button-move_down-disabled.gif")}
                                <input type="image"
                                       src={$up_image|ezimage}
                                       {if $up_enabled}name="CustomActionButton[{$id}_move-up_{$option_item.id}_{$content.basic_options[$option_index|dec()]['id']}]"{/if}
                                       value="{$option_item.id}"
                                       title="{'Move up'|i18n('design/standard/class/datatype')}"
                                       {if $up_enabled|not}disabled="disabled"{/if} />

                                <input type="image"
                                       src={$down_image|ezimage}
                                       {if $down_enabled}name="CustomActionButton[{$id}_move-down_{$option_item.id}_{$content.basic_options[$option_index|inc()]['id']}]"{/if}
                                       value="{$option_item.id}"
                                       title="{'Move down'|i18n('design/standard/class/datatype')}"
                                       {if $down_enabled|not}disabled="disabled"{/if} />
                                {if $option_item.type|eq('optgroup')}
                                    <input type="submit"
                                           class="button btn"
                                           value="{'New option'|i18n('design/standard/class/datatype')}"
                                           name="CustomActionButton[{$id}_new-option_{$option_item.id}]" />
                                {/if}
                            </div>
                        </td>
                    </tr>
                    {foreach $option_item.option_list as $sub_option_index => $sub_option_item}
                        {set $row_count = $row_count|inc()}
                        <tr class="{$bg_colors[$row_count|mod(2)]}">
                            <td colspan="2" style="border-right: 1px solid black;"></td>
                            <td>
                                {$option_index|inc()}.{$sub_option_index|inc()}.
                            </td>
                            <td>
                                <input type="checkbox"
                                       name="ContentClass_owenhancedselection_remove_{$id}[]"
                                       value="{$sub_option_item.id}" />
                            </td>
                            <td>
                                <input type="hidden"
                                       name="ContentClass_owenhancedselection_id_{$id}[]"
                                       value="{$sub_option_item.id}" />
                                <input type="text"
                                       name="ContentClass_owenhancedselection_name_{$id}[{$sub_option_item.id}]"
                                       value="{$sub_option_item.name|wash}" />
                            </td>

                            <td>
                                <input type="text"
                                       name="ContentClass_owenhancedselection_identifier_{$id}[{$sub_option_item.id}]"
                                       value="{$sub_option_item.identifier|wash}" />
                            </td>

                            <td>
                                <div style="white-space: nowrap;">
                                    {set $up_enabled=$sub_option_index|ne(0)
                                         $down_enabled=$sub_option_index|lt(count($option_item.option_list)|dec())
                                         $up_image=cond($up_enabled,"button-move_up.gif","button-move_up-disabled.gif")
                                         $down_image=cond($down_enabled,"button-move_down.gif","button-move_down-disabled.gif")}
                                    <input type="image"
                                           src={$up_image|ezimage}
                                           {if $up_enabled}name="CustomActionButton[{$id}_move-up_{$sub_option_item.id}_{$option_item.option_list[$sub_option_index|dec()]['id']}]"{/if}
                                           value="{$sub_option_item.id}"
                                           title="{'Move up'|i18n('design/standard/class/datatype')}"
                                           {if $up_enabled|not}disabled="disabled"{/if} />

                                    <input type="image"
                                           src={$down_image|ezimage}
                                           {if $down_enabled}name="CustomActionButton[{$id}_move-down_{$sub_option_item.id}_{$option_item.option_list[$sub_option_index|inc()]['id']}]"{/if}
                                           value="{$sub_option_item.id}"
                                           title="{'Move down'|i18n('design/standard/class/datatype')}"
                                           {if $down_enabled|not}disabled="disabled"{/if} />
                                </div>
                            </td>
                        </tr>
                    {/foreach}
                {/foreach}
            </table>
        {/if}

        <div class="block">
            <input type="submit"
                   class="button btn"
                   value="{'New option group'|i18n('design/standard/class/datatype')}"
                   name="CustomActionButton[{$id}_new-option-group]" />
            <input type="submit"
                   class="button btn"
                   value="{'New option'|i18n('design/standard/class/datatype')}"
                   name="CustomActionButton[{$id}_new-option]" />
            <input type="submit"
                   {if count($content.basic_options)|gt(0)}class="button btn"{else}class="btn" disabled="disabled"{/if}
                   value="{'Remove selected option(s)'|i18n('design/standard/class/datatype')}"
                   name="CustomActionButton[{$id}_remove-selected-option]" />
        </div>
    </fieldset>
</div>
<div class="block">
    <div class="element">
        <label>{"Multiple choice"|i18n('design/standard/class/datatype')}:</label>
        <input type="checkbox"
               name="ContentClass_owenhancedselection_multi_{$id}"
               {section show=$content.is_multiselect}checked="checked"{/section} />
    </div>

    <div class="element">
        <label>{"Delimiter"|i18n('design/standard/class/datatype')}:</label>
        <input type="text"
               name="ContentClass_owenhancedselection_delimiter_{$id}"
               value="{$content.delimiter|wash}"
               size="5" />
    </div>

    <div class="break"></div>
</div>

<div class="block">
    <label>{"Database query"|i18n('design/standard/class/datatype')}:</label>
    <p>{"To create a simple option list"|i18n('design/standard/class/datatype')}:</p>
    <pre>SELECT field1 AS identifier, field2 AS name
FROM table1</pre>
    <p>{"To create an option list with group"|i18n('design/standard/class/datatype')}:</p>
    <pre>SELECT field1 AS g_identifier, field2 AS g_name, field3 AS identifier, field4 AS name
FROM table1</pre>
    <textarea rows="5"
              cols="80"
              class="box"
              name="ContentClass_owenhancedselection_query_{$id}">{$content.query|wash}</textarea>
</div>       

{undef}