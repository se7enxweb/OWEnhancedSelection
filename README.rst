OWEnhancedSelection
===============

extended_attribute_filter exemple :
{def $children_list = fetch( 'content', 'list', hash( 
        'parent_node_id', $node.node_id,
        'sort_by', $node.sort_array,
        'extended_attribute_filter', hash(
            'id', 'enhancedselection',
            'params', array(
                hash( 
                    'attribute', 'my_class/my_attribute',
                    'filter', '=',
                    'values', 'my_value',
                )
            )
        ) 
    ) )}

attribute: class attribute to filter
filter: '=', '!=', 'in' or 'not in'
values: can be a string or an array
