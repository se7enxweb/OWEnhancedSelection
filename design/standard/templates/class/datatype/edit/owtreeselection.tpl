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

        {if count($content.options)|gt(0)}
            <table class="list" cellspacing="0">
                <tr>
                    <th style="width: 1%;" colspan="4">&nbsp;</th>
                    <th>{"Name"|i18n('design/standard/class/datatype')}</th>
                    <th>{"Identifier"|i18n('design/standard/class/datatype')}</th>
                    <th>{"Priority"|i18n('design/standard/class/datatype')}</th>
                    <th style="width: 1%;">&nbsp;</th>
                </tr>
                {foreach $content.options as $option_index => $option_item}
                    {set $row_count = $row_count|inc()}
                    <tr class="{$bg_colors[$row_count|mod(2)]}">
                        <td {if $option_item.options|is_set()}style="border-bottom: 1px solid black"{/if}>
                            {$option_index|inc()}.
                        </td>
                        <td {if $option_item.options|is_set()}style="border-bottom: 1px solid black"{/if}>
                            <input type="checkbox"
                                   name="ContentClass_owtreeselection_remove_{$id}[]"
                                   value="{$option_item.id}" />
                        </td>
                        <td colspan="2"></td>
                        <td>
                            <input type="hidden"
                                   name="ContentClass_owtreeselection_id_{$id}[]"
                                   value="{$option_item.id}" />
                            <input type="hidden"
                                   name="ContentClass_owtreeselection_type_{$id}[{$option_item.id}]"
                                   value="{if $option_item.options|is_set()}group{else}option{/if}" />
                            <input type="text"
                                   name="ContentClass_owtreeselection_name_{$id}[{$option_item.id}]"
                                   value="{$option_item.name|wash}" />
                        </td>
                        <td>
                            <input type="text"
                                   name="ContentClass_owtreeselection_identifier_{$id}[{$option_item.id}]"
                                   value="{$option_item.identifier|wash}" />
                        </td>
                        <td>
                            <input type="text"
                                   name="ContentClass_owtreeselection_priority_{$id}[{$option_item.id}]"
                                   value="{$option_item.priority|wash}"
                                   size="3" />
                        </td>
                        <td>
                            <div style="white-space: nowrap;">
                                {set $up_enabled=$option_row.number|eq(1)|not
												 $down_enabled=$option_row.number|eq(count($content.options))|not
												 $up_image=cond($up_enabled,"button-move_up.gif","button-move_up-disabled.gif")
												 $down_image=cond($down_enabled,"button-move_down.gif","button-move_down-disabled.gif")}
                                <input type="image"
                                       src={$up_image|ezimage}
                                       name="CustomActionButton[{$id}_move-up]"
                                       value="{$option_item.id}"
                                       title="{'Move up'|i18n('design/standard/class/datatype')}"
                                       {if $up_enabled|not}disabled="disabled"{/if} />

                                <input type="image"
                                       src={$down_image|ezimage}
                                       name="CustomActionButton[{$id}_move-down]"
                                       value="{$option_item.id}"
                                       title="{'Move down'|i18n('design/standard/class/datatype')}"
                                       {if $down_enabled|not}disabled="disabled"{/if} />
                                {if $option_item.options|is_set()}
                                    <input type="submit"
                                           class="button btn"
                                           value="{'New option'|i18n('design/standard/class/datatype')}"
                                           name="CustomActionButton[{$id}_new-option_{$option_item.id}]" />
                                {/if}
                            </div>
                        </td>
                    </tr>
                    {foreach $option_item.options as $sub_option_index => $sub_option_item}
                        {set $row_count = $row_count|inc()}
                        <tr class="{$bg_colors[$row_count|mod(2)]}">
                            <td colspan="2" style="border-right: 1px solid black;"></td>
                            <td>
                                {$option_index|inc()}.{$sub_option_index|inc()}.
                            </td>
                            <td>
                                <input type="checkbox"
                                       name="ContentClass_owtreeselection_remove_{$id}[]"
                                       value="{$sub_option_item.id}" />
                            </td>
                            <td>
                                <input type="hidden"
                                       name="ContentClass_owtreeselection_id_{$id}[]"
                                       value="{$sub_option_item.id}" />
                                <input type="hidden" 
                                       name="ContentClass_owtreeselection_parent_{$id}[{$sub_option_item.id}]" 
                                       value="{$option_item.id}" />
                                <input type="text"
                                       name="ContentClass_owtreeselection_name_{$id}[{$sub_option_item.id}]"
                                       value="{$sub_option_item.name|wash}" />
                            </td>

                            <td>
                                <input type="text"
                                       name="ContentClass_owtreeselection_identifier_{$id}[{$sub_option_item.id}]"
                                       value="{$sub_option_item.identifier|wash}" />
                            </td>

                            <td>
                                <input type="text"
                                       name="ContentClass_owtreeselection_priority_{$id}[{$sub_option_item.id}]"
                                       value="{$sub_option_item.priority|wash}"
                                       size="3" />
                            </td>

                            <td>
                                <div style="white-space: nowrap;">
                                    {set $up_enabled=$option_row.number|eq(1)|not
												 $down_enabled=$option_row.number|eq(count($content.options))|not
												 $up_image=cond($up_enabled,"button-move_up.gif","button-move_up-disabled.gif")
												 $down_image=cond($down_enabled,"button-move_down.gif","button-move_down-disabled.gif")}
                                    <input type="image"
                                           src={$up_image|ezimage}
                                           name="CustomActionButton[{$id}_move-up]"
                                           value="{$sub_option_item.id}"
                                           title="{'Move up'|i18n('design/standard/class/datatype')}"
                                           {if $up_enabled|not}disabled="disabled"{/if} />

                                    <input type="image"
                                           src={$down_image|ezimage}
                                           name="CustomActionButton[{$id}_move-down]"
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
                   {if count($content.options)|gt(0)}class="button btn"{else}class="btn" disabled="disabled"{/if}
                   value="{'Remove selected option(s)'|i18n('design/standard/class/datatype')}"
                   name="CustomActionButton[{$id}_remove-selected-option]" />

            {* Sorting 1 option doesn't make sense *}
            <input type="submit"
                   {if count($content.options)|gt(1)}class="button btn"{else}class="btn" disabled="disabled"{/if}
                   value="{'Sort options'|i18n('design/standard/class/datatype')}"
                   name="CustomActionButton[{$id}_sort-option-group]" />

            <select {if count($content.options)|le(1)}disabled="disabled"{/if}
                                                      name="ContentClass_owtreeselection_sort_order_{$id}">
                <option value="alpha_asc">{"A-Z"|i18n('design/standard/class/datatype')}</option>
                <option value="alpha_desc">{"Z-A"|i18n('design/standard/class/datatype')}</option>
                <option value="prior_asc">{"Priority"|i18n('design/standard/class/datatype')}</option>
            </select>
        </div>
    </fieldset>
</div>
<div class="block">
    <div class="element">
        <label>{"Multiple choice"|i18n('design/standard/class/datatype')}:</label>
        <input type="checkbox"
               name="ContentClass_owtreeselection_multi_{$id}"
               {section show=$content.is_multiselect}checked="checked"{/section} />
    </div>

    <div class="element">
        <label>{"Delimiter"|i18n('design/standard/class/datatype')}:</label>
        <input type="text"
               name="ContentClass_owtreeselection_delimiter_{$id}"
               value="{$content.delimiter|wash}"
               size="5" />
    </div>

    <div class="break"></div>
</div>

<div class="block">
    <label>{"Database query"|i18n('design/standard/class/datatype')}:</label>
    <textarea rows="5"
              cols="80"
              class="box"
              name="ContentClass_owtreeselection_query_{$id}">{$content.query|wash}</textarea>
</div>       

{undef}