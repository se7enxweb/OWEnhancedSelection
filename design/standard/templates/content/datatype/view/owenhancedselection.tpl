{def $content=$attribute.content
     $class_content=$attribute.class_content
     $available_options=$class_content.options}
{foreach $content.options as $option}
    {delimiter}{cond( $class_content.delimiter|ne(""), $class_content.delimiter, ", ")}{/delimiter}
    {if $option.optgroup}{$option.optgroup.name|wash}/{/if}{$option.name|wash}
{/foreach}

{/undef}