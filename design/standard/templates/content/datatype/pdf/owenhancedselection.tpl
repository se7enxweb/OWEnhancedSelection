{def $content=$attribute.content
     $class_content=$attribute.class_content
     $available_options=$class_content.options}

{set-block scope=root variable=pdf_text}{*
*}{foreach $content.options as $option}{*
*}{delimiter}{cond( $class_content.delimiter|ne(""), $class_content.delimiter, ezini('Delimiter', 'Default', 'owenhancedselection.ini'))}{/delimiter}{*
*}{if $option.optgroup}{$option.optgroup.name|wash}/{/if}{$option.name|wash}{*
*}{/foreach}{*
*}{/set-block}
{pdf(text, $pdf_text|wash(pdf))}
{undef}