<?php

/*
  Enhanced selection extension for eZ publish 4.x
  Copyright (C) 2003-2008  SCK-CEN (Belgian Nuclear Research Centre)

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License as published by
  the Free Software Foundation; either version 2 of the License, or
  (at your option) any later version.

  This program is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  GNU General Public License for more details.
 */


/* !
  \class   OWEnhancedSelectionDBOption owenhancedselection.php
  \ingroup eZDatatype
  \brief   Handles the persistent object for the datatype owenhancedselection.
  \version 3.0
  \date    Tuesday 16 August 2005 9:56:00 am
  \author  Madeline Veyrenc
 */

class OWEnhancedSelectionDBOption {

    const
        OPTION_TYPE = 'option',
        OPTGROUP_TYPE = 'optgroup';

    public static $localeCode = false;
    protected $type;
    protected $identifier;
    protected $name;
    protected $optionList = array();
    protected $optgroup;

    public static function definition() {
        return array(
            'fields' => array(
                'type' => array(
                    'name' => 'type',
                    'datatype' => 'string',
                    'default' => '',
                    'required' => true ),
                'identifier' => array(
                    'name' => 'identifier',
                    'datatype' => 'string',
                    'default' => '',
                    'required' => true ),
                'name' => array(
                    'name' => 'name',
                    'datatype' => 'string',
                    'default' => '',
                    'required' => true ),
                'option_list' => array(
                    'name' => 'optionList',
                    'datatype' => 'array',
                    'default' => '',
                    'required' => false ),
                'optgroup' => array(
                    'name' => 'optgroup',
                    'datatype' => 'OWEnhancedSelectionDBOption',
                    'default' => '',
                    'required' => false ),
            ),
            'class_name' => 'OWEnhancedSelectionDBOption',
            'function_attributes' => array(
                'is_optgroup' => 'isOptgroup',
                'is_option' => 'isOption',
                'has_option' => 'hasOption'
            )
        );
    }

    /**
     * Returns the attributes for this object, taken from the definition fields
     * and function attributes.
     *
     * @see eZPersistentObject::definition()
     *
     * @return array
     */
    public function attributes() {
        $def = $this->definition();
        $attrs = array_keys( $def["fields"] );
        if ( isset( $def["function_attributes"] ) ) {
            $attrs = array_unique( array_merge( $attrs, array_keys( $def["function_attributes"] ) ) );
        }
        if ( isset( $def["functions"] ) ) {
            $attrs = array_unique( array_merge( $attrs, array_keys( $def["functions"] ) ) );
        }
        return $attrs;
    }

    /**
     * Checks if $attr is part of the definition fields or function attributes.
     *
     * @param string $attr
     * @return bool
     */
    public function hasAttribute( $attr ) {
        $def = $this->definition();
        $has_attr = isset( $def["fields"][$attr] );
        if ( !$has_attr and isset( $def["function_attributes"] ) ) {
            $has_attr = isset( $def["function_attributes"][$attr] );
        }
        if ( !$has_attr and isset( $def["functions"] ) ) {
            $has_attr = isset( $def["functions"][$attr] );
        }
        return $has_attr;
    }

    /**
     * Returns the attribute data for $attr, this is either returned from the
     * member variables or a member function depending on whether the definition
     * field or function attributes matched.
     *
     * @param string $attr
     * @param bool $noFunction
     * @return mixed
     */
    public function attribute( $attr, $noFunction = false ) {
        $def = $this->definition();
        $attrFunctions = isset( $def["function_attributes"] ) ? $def["function_attributes"] : null;
        if ( $noFunction === false && isset( $attrFunctions[$attr] ) ) {
            $functionName = $attrFunctions[$attr];
            if ( method_exists( $this, $functionName ) ) {
                return $this->$functionName();
            }

            eZDebug::writeError( 'Could not find function : "' . get_class( $this ) . '::' . $functionName . '()".', __METHOD__ );
            return null;
        }

        $fields = $def["fields"];
        if ( isset( $fields[$attr] ) ) {
            $attrName = $fields[$attr];
            if ( isset( $attrName['name'] ) ) {
                $attrName = $attrName['name'];
            }
            return $this->$attrName;
        }

        if ( isset( $def["functions"][$attr] ) ) {
            return $this->$def["functions"][$attr]();
        }

        eZDebug::writeError( "Attribute '$attr' does not exist", $def['class_name'] . '::attribute' );
        return null;
    }

    /**
     * Sets the attribute $attr to the value $val.
     *
     * The attribute must be present in the objects definition fields or set functions.
     *
     * @param string $attr
     * @param mixed $val
     * @return void
     */
    public function setAttribute( $attr, $val ) {
        $def = $this->definition();
        $fields = $def["fields"];
        if ( isset( $fields[$attr] ) ) {
            $attrName = $fields[$attr];
            if ( is_array( $attrName ) ) {
                $attrName = $attrName['name'];
            }
            $this->$attrName = $val;
        }
        else {
            eZDebug::writeError( "Undefined attribute '$attr', cannot set", $def['class_name'] );
        }
    }
    
    public function remove() {}

    /**
     * Test if the object is an option group
     * 
     * @return type
     */
    protected function isOptgroup() {
        return $this->attribute( 'type' ) == self::OPTGROUP_TYPE;
    }

    /**
     * Test if the object is an option
     * 
     * @return boolean
     */
    protected function isOption() {
        return $this->attribute( 'type' ) == self::OPTION_TYPE;
    }

    /**
     * Test if the option group has option
     * 
     * @return boolean
     */
    protected function hasOption() {
        return (bool) $this->attribute( 'option_list' );
    }

    /**
     * Object ti string convertion
     * 
     * @return string
     */
    public function __toString() {
        return $this->attribute( 'identifier' );
    }

}
