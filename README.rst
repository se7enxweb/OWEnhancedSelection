================================================
OWEnhancedSelection for eZ Publish documentation
================================================

.. image:: https://github.com/lolautruche/SQLIImport/raw/master/doc/images/Open-Wide_logo.png
    :align: center

:Extension: OW Enhanced Selection v1.0
:Requires: eZ Publish 4.x.x (not tested on 3.X)
:Author: Open Wide http://www.openwide.fr

Presentation
============
This extension provides a data type for single or multiple selections with more advanced features than the default, such as indexing, managing groups of options or creating selections from a SQL query

LICENCE
-------
This eZ Publish extension is provided *as is*, in GPL v2 (see LICENCE).

Installation
============

1. Clone the repository in the extension folder :

.. code-block:: sh

$ git clone https://github.com/Open-Wide/OWEnhancedSelection.git extension/owenhancedselection

2. Enable the extension in the site.ini.append.php :

.. code-block:: php

ActiveExtensions[]=owenhancedselection

3. Update the autoload arrays and clear cache :

.. code-block:: sh

$ bin/php/ezpgenerateautoloads.php --extension
$ bin/php/ezcache.php --clear-all

4. Create the following table in your database :

.. code-block:: sql

CREATE TABLE owenhancedselection (
  id int(11) NOT NULL AUTO_INCREMENT,
  contentclassattribute_id int(11) NOT NULL,
  type varchar(8) DEFAULT NULL,
  optgroup_id int(11) DEFAULT NULL,
  serialized_name_list longtext,
  identifier varchar(200) DEFAULT NULL,
  priority int(11) DEFAULT NULL,
  PRIMARY KEY (id)
)

Usage
=====

Add an owenhancedselection attribute in your content class. Configure the content of the selection by using option list part or query part :

.. image:: https://github.com/lolautruche/SQLIImport/raw/master/doc/images/owenhancedselection.png
    :align: center

Attribute filter
================

If you want to filter content based on selected values in owenhancedselection datatype, you can use this extended_attribute_filter :

.. code-block:: 

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

*filter* parameter can take these values : '=', '!=', 'in', 'not in' or regexp
*value* can be a string or an array or string. Strings must represent an option identifier.

Indexing
========

You have 3 filters for Solr queries :
* my_class/my_attribute/optgroup_identifier : list of the identifiers of option groups of selected options
* my_class/my_attribute/optgroup_name : list of the names of option groups of selected options
* my_class/my_attribute/identifier : list of the identifiers of selected options
* my_class/my_attribute/name : list of the names of selected options
