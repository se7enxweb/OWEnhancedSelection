{def $content=$class_attribute.content
     $row_count = 0
     $bg_colors = array('bglight', 'bgdark')}
     
<label>{"Option list"|i18n('design/standard/class/datatype')}:</label>
<table class="list" cellspacing="0">
    <tr>
        <th style="width: 1%;" colspan="2">&nbsp;</th>
        <th>{"Name"|i18n('design/standard/class/datatype')}</th>
        <th>{"Identifier"|i18n('design/standard/class/datatype')}</th>
    </tr>

    {foreach $content.options as $option_index => $option_item}
        {set $row_count = $row_count|inc()}
        <tr class="{$bg_colors[$row_count|mod(2)]}">
            <td colspan="2">{if $option_item.type|eq('optgroup')}<b>{/if}{$option_index|inc()}.{if $option_item.type|eq('optgroup')}</b>{/if}</td>
            <td>{if $option_item.type|eq('optgroup')}<b>{/if}{first_set($option_item.name|wash,"&nbsp;")}{if $option_item.type|eq('optgroup')}</b>{/if}</td>
            <td>{if $option_item.type|eq('optgroup')}<b>{/if}{first_set($option_item.identifier|wash,"&nbsp;")}{if $option_item.type|eq('optgroup')}</b>{/if}</td>
        </tr>
        {foreach $option_item.option_list as $sub_option_index => $sub_option_item}
            {set $row_count = $row_count|inc()}
            <tr class="{$bg_colors[$row_count|mod(2)]}">
                <td></td>
                <td>{$option_index|inc()}.{$sub_option_index|inc()}.</td>
                <td>{first_set($sub_option_item.name|wash,"&nbsp;")}</td>
                <td>{first_set($sub_option_item.identifier|wash,"&nbsp;")}</td>
            </tr>
        {/foreach}
    {/foreach}
</table>

<div class="block">
    <div class="element">
        <label>{"Multiple choice"|i18n('design/standard/class/datatype')}:</label>
        <p>{cond($content.is_multiselect,"Yes"|i18n('design/standard/class/datatype'),"No"|i18n('design/standard/class/datatype'))}</p>
    </div>

    <div class="element">
        <label>{"Delimiter"|i18n('design/standard/class/datatype')}:</label>
        <p style="white-space: pre;">'{$content.delimiter|wash}'</p>
    </div>

    <div class="break"></div>
</div>

<div class="block">
    <label>{"Database query"|i18n('design/standard/class/datatype')}:</label>
    <p>{$content.query|wash|nl2br}</p>
</div>
{undef}