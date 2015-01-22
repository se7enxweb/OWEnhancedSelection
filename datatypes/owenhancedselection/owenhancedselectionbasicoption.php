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
  \class   OWEnhancedSelectionBasicOption owenhancedselection.php
  \ingroup eZDatatype
  \brief   Handles the persistent object for the datatype owenhancedselection.
  \version 3.0
  \date    Tuesday 16 August 2005 9:56:00 am
  \author  Madeline Veyrenc
 */

class OWEnhancedSelectionBasicOption extends eZPersistentObject {

    const
        OPTION_TYPE = 'option',
        OPTGROUP_TYPE = 'optgroup';

    public static $localeCode = false;

    public function __construct( $row ) {
        if( is_array( $row ) ) {
            $this->eZPersistentObject( $row );
            $this->NameList = new eZSerializedObjectNameList();
            if( isset( $row['serialized_name_list'] ) ) {
                $this->NameList->initFromSerializedList( $row['serialized_name_list'] );
            } elseif( isset( $row['name'] ) ) {
                $this->NameList->initFromString( $row['name'] );
            } else {
                $this->NameList->initDefault();
            }
        }
    }

    public static function definition() {
        return array(
            'fields' => array(
                'id' => array(
                    'name' => 'id',
                    'datatype' => 'integer',
                    'default' => null,
                    'required' => true ),
                'contentclassattribute_id' => array(
                    'name' => 'contentclassattribute_id',
                    'datatype' => 'integer',
                    'default' => null,
                    'required' => true ),
                'type' => array(
                    'name' => 'type',
                    'datatype' => 'string',
                    'default' => '',
                    'required' => true ),
                'optgroup_id' => array(
                    'name' => 'optgroup_id',
                    'datatype' => 'integer',
                    'default' => 0,
                    'required' => true ),
                'serialized_name_list' => array(
                    'name' => 'serialized_name_list',
                    'datatype' => 'string',
                    'default' => '',
                    'required' => true ),
                'identifier' => array(
                    'name' => 'identifier',
                    'datatype' => 'string',
                    'default' => '',
                    'required' => true ),
                'priority' => array(
                    'name' => 'priority',
                    'datatype' => 'integer',
                    'default' => false,
                    'required' => true )
            ),
            'keys' => array( 'id' ),
            'increment_key' => 'id',
            'class_name' => 'OWEnhancedSelectionBasicOption',
            'name' => 'owenhancedselection',
            'function_attributes' => array(
                'is_optgroup' => 'isOptgroup',
                'is_option' => 'isOption',
                'optgroup' => 'optgroup',
                'has_option' => 'hasOption',
                'option_list' => 'optionList',
                'name' => 'name',
                'nameList' => 'nameList'
            ),
            'set_functions' => array(
                'name' => 'setName'
            ),
            'sort' => array( 'priority' => 'asc' ),
            'grouping' => array()
        );
    }

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
     * Return the option group related to the object if exists
     * 
     * @return OWEnhancedSelectionBasicOption|null
     */
    protected function optgroup() {
        return self::fetch( array( 'id' => $this->attribute( 'optgroup_id' ) ) );
    }

    /**
     * Test if the option group has option
     * 
     * @return boolean
     */
    protected function hasOption() {
        if( $this->attribute( 'id' ) == null ) {
            return false;
        }
        return self::countList( array(
                'optgroup_id' => $this->attribute( 'id' ) )
            ) > 0;
    }

    /**
     * Return all option of the option group
     * 
     * @return boolean
     */
    protected function optionList() {
        $objectList = self::fetchList( array(
                'optgroup_id' => $this->attribute( 'id' ) )
        );
        foreach( $objectList as $index => $object ) {
            if( $object->attribute( 'priority' ) != $index + 1 ) {
                $object->setAttribute( 'priority', $index + 1 );
            }
        }
        return $objectList;
    }

    /**
      Returns the object name in \a $languageLocale language.
      Uses siteaccess language list or 'always available' language if \a $languageLocale is 'false'.

      @return string
     */
    public function name( $languageLocale = false ) {
        if( !$languageLocale ) {
            $languageLocale = self::$localeCode;
        }
        return $this->NameList->name( $languageLocale );
    }

    /**
     * Set object name
     * 
     * @param string $name
     * @param string $languageLocale
     */
    function setName( $name, $languageLocale = false ) {
        if( !$languageLocale ) {
            $languageLocale = $this->topPriorityLanguageLocale();
        }
        $this->NameList->setNameByLanguageLocale( $name, $languageLocale );
    }

    /**
     * Return name list array
     * 
     * @return array
     */
    function nameList() {
        return $this->NameList->nameList();
    }

    /**
     * Fetch an object by custom conditions
     * 
     * @param array $conds
     * @return INCA_eCancerJoomlaContentMapping
     */
    static function fetch( array $conds ) {
        return self::fetchObject( self::definition(), null, $conds );
    }

    /**
     * Search all objects with custom conditions
     *
     * @param array $conds
     * @param integer $limit
     * @param integer $offset
     * @param boolean $asObject
     * @return array
     */
    static function fetchList( $conds = array(), $limit = false, $offset = false, $asObject = true ) {
        $sortArr = null;
        $limitArr = null;

        if( (int) $limit != 0 ) {
            $limitArr = array(
                'limit' => $limit,
                'offset' => $offset );
        }
        $objectList = eZPersistentObject::fetchObjectList( self::definition(), null, $conds, $sortArr, $limitArr, $asObject, null, null, null, null );
        return $objectList;
    }

    /**
     * Count all object with custom conditions
     *
     * @param array $conds
     * @return interger
     */
    static function countList( $conds = array() ) {
        $objectList = eZPersistentObject::count( self::definition(), $conds );
        return $objectList;
    }

    /**
     * Max value of field with custom conditions
     *
     * @param string $field
     * @param array $conds
     * @return interger
     */
    static function max( $field, $conds = array() ) {
        $customFields = array( array( 'operation' => 'MAX( ' . $field . ' )', 'name' => 'max' ) );
        $rows = eZPersistentObject::fetchObjectList( self::definition(), array(), $conds, array(), null, false, false, $customFields );
        return $rows[0]['max'];
    }

    /**
     * Returns the list of all options of a content class attribute
     * @param interger $contentClassAttributeID
     * @return array
     */
    static function fetchAttributeOptionlist( $contentClassAttributeID ) {
        $objectList = self::fetchList( array(
                'contentclassattribute_id' => $contentClassAttributeID,
                'optgroup_id' => 0
            ) );
        foreach( $objectList as $index => $object ) {
            if( $object->attribute( 'priority' ) != ($index + 1) * 10 ) {
                $object->setAttribute( 'priority', ($index + 1) * 10 );
            }
        }
        return $objectList;
    }

    static function createOrUpdate( $row ) {
        if( array_key_exists( 'id', $row ) ) {
            $cond = array( 'id' => $row['id'] );
        } elseif( array_key_exists( 'contentclassattribute_id', $row ) && array_key_exists( 'identifier', $row ) ) {
            $cond = array(
                'contentclassattribute_id' => $row['contentclassattribute_id'],
                'identifier' => $row['identifier'],
            );
        }
        $object = static::fetch( $cond );
        if( $object ) {
            foreach( $row as $attribute => $value ) {
                $object->setAttribute( $attribute, $value );
            }
        } else {
            $object = new self( $row );
        }
        $object->store();
        return $object;
    }

    /**
     * Store object
     * 
     * @param boolean $store_childs
     * @param array $fieldFilters
     */
    function store( $store_childs = false, $fieldFilters = null ) {
        if( $this->attribute( 'identifier' ) == '' ) {
            $this->setAttribute( 'identifier', $this->generateIdentifier() );
        }
        if( $this->attribute( 'type' ) == '' ) {
            $this->setAttribute( 'type', self::OPTION_TYPE );
        }
        $this->setAttribute( 'serialized_name_list', $this->NameList->serializeNames() );
        if( $this->attribute( 'priority' ) == 0 ) {
            $lastPriority = static::max( 'priority', array(
                    'contentclassattribute_id' => $this->attribute( 'contentclassattribute_id' ),
                    'optgroup_id' => $this->attribute( 'optgroup_id' )
                ) );
            $lastPriority += $this->attribute( 'optgroup_id' ) == 0 ? 10 : 1;
            $this->setAttribute( 'priority', $lastPriority );
        }
        parent::store( $store_childs, $fieldFilters );
    }

    /**
     * Remove object and related objects
     * 
     * @param array $conditions
     * @param array $extraConditions
     */
    public function remove( $conditions = null, $extraConditions = null ) {
        if( $this->attribute( 'has_option' ) ) {
            foreach( $this->attribute( 'option_list' ) as $option ) {
                $option->remove();
            }
        }
        parent::remove( $conditions, $extraConditions );
    }

    /**
     * Swap two options
     */
    public function swapOptions( $optionID1, $optionID2 ) {
        $option1 = self::fetch( array( 'id' => $optionID1 ) );
        $option2 = self::fetch( array( 'id' => $optionID2 ) );
        $priority2 = $option1->attribute( 'priority' );
        $option1->setAttribute( 'priority', $option2->attribute( 'priority' ) );
        $option2->setAttribute( 'priority', $priority2 );
        $option1->store();
        $option2->store();
    }

    /**
     * Wrapper for eZSerializedObjectNameList::topPriorityLanguageLocale.
     * 
     * @return string
     */
    protected function topPriorityLanguageLocale() {
        return $this->NameList->topPriorityLanguageLocale();
    }

    protected function generateIdentifier() {
        $name = $this->attribute( 'name' );
        if( empty( $name ) ) {
            return '';
        }
        $identifier = $name;
        $trans = eZCharTransform::instance();
        $generatedIdentifier = $trans->transformByGroup( $identifier, 'identifier' );
        $identifierCount = self::countList( array(
                'contentclassattribute_id' => $this->attribute( 'contentclassattribute_id' ),
                'identifier' => $generatedIdentifier,
            ) );
        if( $identifierCount > 0 ) {
            $generatedIdentifier .= "_$identifierCount";
        }
        return $generatedIdentifier;
    }

    public static function addOption( $newOptionParameters ) {
        if( isset( $newOptionParameters['optgroup_identifier'] ) ) {
            $optgroup = self::fetch( array(
                    'contentclassattribute_id' => $newOptionParameters['contentclassattribute_id'],
                    'identifier' => $newOptionParameters['optgroup_identifier'],
                    'type' => 'optgroup'
                ) );
            if( !is_object( $optgroup ) || (!$optgroup instanceof OWEnhancedSelectionBasicOption ) ) {
                eZDebug::writeError( "The optgroup's identifier '" . $newOptionParameters['optgroup_identifier'] . "' can't be found", __METHOD__ );
                return false;
            }
            unset( $newOptionParameters['optgroup_identifier'] );
            $newOptionParameters['optgroup_id'] = $optgroup->attribute( 'id' );
        }
        return self::addItem( $newOptionParameters );
    }

    public static function addOptgroup( $newOptionParameters ) {
        $newOptionParameters['type'] = 'optgroup';
        return self::addItem( $newOptionParameters );
    }

    public static function addItem( $newOptionParameters ) {
        $requiredFieldList = array( 'identifier', 'name' );
        foreach( $requiredFieldList as $requiredField ) {
            if( !isset( $newOptionParameters[$requiredField] ) ) {
                eZDebug::writeError( "Field '$requiredField' is not set", __METHOD__ );
                return false;
            }
            $option[$requiredField] = $newOptionParameters[$requiredField];
        }
        $newOption = self::createOrUpdate( $newOptionParameters );

        if( !is_object( $newOption ) ) {
            eZDebug::writeDebug( "New option not added, return is not an object", __METHOD__ );
            return false;
        }
        return $newOption;
    }

    public static function removeOption( $contentClassAttribute, $optionIdentifier ) {
        return self::removeItem( $contentClassAttribute, $optionIdentifier );
    }

    public static function removeOptgroup( $contentClassAttribute, $optionIdentifier ) {
        return self::removeItem( $contentClassAttribute, $optionIdentifier, 'optgroup' );
    }

    public static function removeItem( $contentClassAttribute, $optionIdentifier, $type = 'option' ) {
        $option = self::fetch( array(
                'contentclassattribute_id' => $contentClassAttribute->attribute( 'id' ),
                'identifier' => $optionIdentifier,
                'type' => $type
            ) );

        if( !$option ) {
            eZDebug::writeDebug( "There were no option to remove for the identifier '$optionIdentifier'", __METHOD__ );
            return false;
        }
        $option->remove();
        return true;
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
