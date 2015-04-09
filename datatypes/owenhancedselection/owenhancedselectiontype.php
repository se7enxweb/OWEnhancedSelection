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
  \class   OWEnhancedSelectionType owenhancedselectiontype.php
  \ingroup eZDatatype
  \brief   Handles the datatype owenhancedselection.
  \version 3.0
  \date    Tuesday 16 August 2005 9:56:00 am
  \author  Madeline Veyrenc
 */

class OWEnhancedSelectionType extends eZDataType {

    const DATATYPESTRING = 'owenhancedselection';
    const CONTENT_CLASS_STORAGE = 'data_text5';

    protected $defaultDelimiter;

    function __construct() {
        $this->eZDataType( self::DATATYPESTRING, ezpI18n::tr( 'kernel/classes/datatypes', 'Enhanced selection (OW)', 'Datatype name' ), array(
            'serialize_supported' => true,
            'object_serialize_map' => array( 'data_text' => 'selection' )
        ) );

        $INI = eZINI::instance( 'owenhancedselection.ini' );
        $this->defaultDelimiter = $INI->variable( 'Delimiter', 'Default' );
    }

    /*     * ******
     * CLASS *
     * ****** */

    function validateClassAttributeHTTPInput( $http, $base, $classAttribute ) {
        $id = $classAttribute->attribute( 'id' );

        $identifiersName = join( '_', array( $base, 'owenhancedselection_identifier', $id ) );
        if ( $http->hasPostVariable( $identifiersName ) ) {
            $identifiers = $http->postVariable( $identifiersName );
            foreach ( $identifiers as $identifier ) {
                if ( empty( $identifier ) ) {
                    return eZInputValidator::STATE_INVALID;
                }
            }
        }

        $queryName = join( '_', array( $base, 'owenhancedselection_query', $id ) );
        if ( $http->hasPostvariable( $queryName ) ) {
            $query = trim( $http->postVariable( $queryName ) );
            if ( !empty( $query ) ) {
                if ( $this->isDbQueryValid( $query ) !== true ) {
                    return eZInputValidator::STATE_INVALID;
                }
            }
        }

        return eZInputValidator::STATE_ACCEPTED;
    }

    function fetchClassAttributeHTTPInput( $http, $base, $classAttribute ) {
        $content = $classAttribute->content();
        $id = $classAttribute->attribute( 'id' );

        $idArrayName = "{$base}_owenhancedselection_id_{$id}";
        $nameArrayName = "{$base}_owenhancedselection_name_{$id}";
        $identifierArrayName = "{$base}_owenhancedselection_identifier_{$id}";

        $multiSelectName = "{$base}_owenhancedselection_multi_{$id}";
        $delimiterName = "{$base}_owenhancedselection_delimiter_{$id}";

        $queryName = "{$base}_owenhancedselection_query_{$id}";

        $deserializedName = "{$base}_owenhancedselection_deserialized_{$id}";

        if ( $http->hasPostVariable( $idArrayName ) ) {
            $idArray = $http->postVariable( $idArrayName );
            $nameArray = $http->postVariable( $nameArrayName );
            $identifierArray = $http->postVariable( $identifierArrayName );
            foreach ( $idArray as $id ) {
                $name = isset( $nameArray[$id] ) ? $nameArray[$id] : '';
                $identifier = isset( $identifierArray[$id] ) ? $identifierArray[$id] : '';
                $option = OWEnhancedSelectionBasicOption::fetch( array( 'id' => $id ) );
                if ( $option instanceof OWEnhancedSelectionBasicOption ) {
                    $option->setName( $name, $classAttribute->editLocale() );
                    $option->setAttribute( 'identifier', $identifier );
                    $option->store();
                }
            }
        }

        if ( $http->hasPostVariable( $multiSelectName ) ) {
            $content['is_multiselect'] = 1;
        } else if ( $http->hasPostVariable( 'ContentClassHasInput' ) ) {
            $content['is_multiselect'] = 0;
        }

        if ( $http->hasPostVariable( $delimiterName ) ) {
            $content['delimiter'] = $http->postVariable( $delimiterName );
        }

        if ( $http->hasPostVariable( $queryName ) ) {
            $content['query'] = trim( $http->postVariable( $queryName ) );

            if ( $http->hasPostVariable( $deserializedName ) ) {
                $content['is_deserialized'] = 1;
            } else if ( $http->hasPostVariable( 'ContentClassHasInput' ) ) {
                $content['is_deserialized'] = 0;
            }
        }

        $classAttribute->setContent( $content );
        $classAttribute->store();

        return true;
    }

    function classAttributeContent( $classAttribute ) {
        $content = @unserialize( $classAttribute->attribute( self::CONTENT_CLASS_STORAGE ) );
        if ( empty( $content ) ) {
            $content = array(
                'basic_options' => array(),
                'is_multiselect' => 0,
                'delimiter' => '',
                'query' => '',
                'db_options' => array(),
                'options' => array(),
                'options_by_identifier' => array()
            );
        } else {
            $content['basic_options'] = OWEnhancedSelectionBasicOption::fetchAttributeOptionlist( $classAttribute->attribute( 'id' ) );
            $content['db_options'] = $this->getDbOptions( $content );
            $content['options'] = empty( $content['db_options'] ) ? $content['basic_options'] : $content['db_options'];
            $optionsByidentifier = array();
            foreach ( $content['options'] as $option ) {
                $optionsByidentifier[$option->attribute( 'identifier' )] = $option;
                if ( $option->attribute( 'has_option' ) ) {
                    foreach ( $option->attribute( 'option_list' ) as $subOption ) {
                        $optionsByidentifier[$subOption->attribute( 'identifier' )] = $subOption;
                    }
                }
            }
            $content['options_by_identifier'] = $optionsByidentifier;
        }
        return $content;
    }

    function preStoreClassAttribute( $classAttribute, $version ) {
        $content = $classAttribute->content();
        unset( $content['basic_options'] );
        unset( $content['db_options'] );
        unset( $content['options'] );
        unset( $content['options_by_identifier'] );
        $classAttribute->setAttribute( self::CONTENT_CLASS_STORAGE, serialize( $content ) );
        $classAttribute->setContent(  $content );
    }

    function deleteStoredClassAttribute( $classAttribute, $version = null ) {
        if ( $version === null ) {
            $content = $classAttribute->content();
            foreach ( $content['options'] as $option ) {
                $option->remove();
            }
        }
    }

    function cloneClassAttribute( $oldClassAttribute, $newClassAttribute ) {
        $newClassAttribute->store();
        $content = $oldClassAttribute->content();
        foreach ( $content['options'] as $option ) {
            if( $option instanceof OWEnhancedSelectionDBOption) {
                break;
            }
            $newOption = clone( $option );
            $newOption->setAttribute( 'id', null );
            $newOption->setAttribute( 'contentclassattribute_id', $newClassAttribute->attribute( 'id' ) );
            $newOption->store();
            if ( $option->attribute( 'has_option' ) ) {
                foreach ( $option->attribute( 'option_list' ) as $subOption ) {
                    $newSubOption = clone( $subOption );
                    $newSubOption->setAttribute( 'id', null );
                    $newSubOption->setAttribute( 'contentclassattribute_id', $newClassAttribute->attribute( 'id' ) );
                    $newSubOption->setAttribute( 'optgroup_id', $newOption->attribute( 'id' ) );
                    $newSubOption->store();
                }
            }
        }
    }

    function customClassAttributeHTTPAction( $http, $action, $classAttribute ) {
        $id = $classAttribute->attribute( 'id' );
        $base = "ContentClass";
        $content = $classAttribute->content();

        $idArrayName = "{$base}_owenhancedselection_id_{$id}";
        $idArray = array();

        if ( $http->hasPostVariable( $idArrayName ) ) {
            $idArray = $http->postVariable( $idArrayName );
        }

        $actionlist = explode( "_", $action );
        $processAction = $actionlist[0];

        switch ( $processAction ) {
            case 'new-option-group':
                $row = array(
                    'id' => null,
                    'contentclassattribute_id' => $classAttribute->attribute( 'id' ),
                    'name' => '',
                    'identifier' => '',
                    'type' => OWEnhancedSelectionBasicOption::OPTGROUP_TYPE
                );
                $option = OWEnhancedSelectionBasicOption::createOrUpdate( $row );
                break;
            case 'new-option':
                $row = array(
                    'id' => null,
                    'contentclassattribute_id' => $classAttribute->attribute( 'id' ),
                    'name' => '',
                    'identifier' => '',
                    'type' => OWEnhancedSelectionBasicOption::OPTION_TYPE
                );
                if ( isset( $actionlist[1] ) ) {
                    $row['optgroup_id'] = $actionlist[1];
                }
                $option = OWEnhancedSelectionBasicOption::createOrUpdate( $row );
                break;

            case 'remove-selected-option':
                $removeArrayName = "{$base}_owenhancedselection_remove_{$id}";

                if ( $http->hasPostVariable( $removeArrayName ) ) {
                    $removeArray = $http->postVariable( $removeArrayName );

                    foreach ( $removeArray as $removeID ) {
                        $option = OWEnhancedSelectionBasicOption::fetch( array( 'id' => $removeID ) );
                        if ( $option instanceof OWEnhancedSelectionBasicOption ) {
                            $option->remove();
                        }
                    }
                }
                break;

            case 'move-up':
                if ( isset( $actionlist[1] ) && isset( $actionlist[2] ) ) {
                    OWEnhancedSelectionBasicOption::swapOptions( $actionlist[1], $actionlist[2] );
                }
                break;

            case 'move-down':
                if ( isset( $actionlist[1] ) && isset( $actionlist[2] ) ) {
                    OWEnhancedSelectionBasicOption::swapOptions( $actionlist[1], $actionlist[2] );
                }
                break;

            default:
                eZDebug::writeError( "Unknown class HTTP action: $action", "OWEnhancedSelectionType" );
                break;
        }

        $classAttribute->setContent( $content );
        $classAttribute->store();

        $http->setPostVariable( $idArrayName, $idArray );
    }

    /*     * *******
     * OBJECT *
     * ******* */

    function validateObjectAttributeHTTPInput( $http, $base, $contentObjectAttribute ) {
        $status = $this->validateAttributeHTTPInput( $http, $base, $contentObjectAttribute, false );

        return $status;
    }

    function fetchObjectAttributeHTTPInput( $http, $base, $contentObjectAttribute ) {
        $id = $contentObjectAttribute->attribute( 'id' );
        $classContent = $contentObjectAttribute->classContent();
        $content = $contentObjectAttribute->content();

        $selectionName = join( '_', array( $base, 'owenhancedselection_selection',
            $id ) );

        if ( $http->hasPostVariable( $selectionName ) ) {
            $selection = $http->postVariable( $selectionName );

            $content['identifiers'] = $selection;
        } else if ( $classContent['is_multiselect'] == 1 ) {
            $content['identifiers'] = array();
        }

        $contentObjectAttribute->setContent( $content );

        return true;
    }

    function objectAttributeContent( $contentObjectAttribute ) {
        OWEnhancedSelectionBasicOption::$localeCode = $contentObjectAttribute->attribute( 'language_code' );
        $optionList = array();
        $contentString = $contentObjectAttribute->attribute( 'data_text' );
        $identifierList = unserialize( $contentString );
        if ( !is_array( $identifierList ) ) {
            $identifierList = array();
        }
        $classAttributeContent = $this->classAttributeContent( $contentObjectAttribute->attribute( 'contentclass_attribute' ) );
        $availableOptions = $classAttributeContent['options_by_identifier'];
        foreach ( $identifierList as $identifier ) {
            if ( isset( $availableOptions[$identifier] ) ) {
                $optionList[$identifier] = $availableOptions[$identifier];
            }
        }
        $content = array(
            'options' => array_values( $optionList ),
            'options_by_identifier' => $optionList,
            'identifiers' => array_keys( $optionList )
        );
        $classContent = $contentObjectAttribute->classContent();
        $content['to_string'] = $this->_title( $content, $classContent );

        return $content;
    }

    function hasObjectAttributeContent( $contentObjectAttribute ) {
        $contentString = $contentObjectAttribute->attribute( 'data_text' );
        if ( empty( $contentString ) ) {
            return false;
        }
        $selection = unserialize( $contentString );
        if ( !is_array( $selection ) or count( $selection ) == 0 ) {
            return false;
        }
        return true;
    }

    function storeObjectAttribute( $objectAttribute ) {
        $content = $objectAttribute->content();
        $contentString = serialize( $content['identifiers'] );
        $objectAttribute->setAttribute( 'data_text', $contentString );
    }

    function customObjectAttributeHTTPAction( $http, $action, $objectAttribute, $parameters ) {
        
    }

    /*     * ***********
     * COLLECTION *
     * *********** */

    function validateCollectionAttributeHTTPInput( $http, $base, $objectAttribute ) {
        $status = $this->validateAttributeHTTPInput( $http, $base, $objectAttribute, true );

        return $status;
    }

    function fetchCollectionAttributeHTTPInput( $collection, $collectionAttribute, $http, $base, $objectAttribute ) {

        $id = $objectAttribute->attribute( 'id' );
        $classContent = $objectAttribute->classContent();
        $nameArray = array();

        $selectionName = join( '_', array( $base, 'owenhancedselection_selection',
            $id ) );
        $selection = $http->postVariable( $selectionName );

        if ( $http->hasPostVariable( $selectionName ) ) {
            $selection = $http->postVariable( $selectionName );

            if ( count( $selection ) > 0 && $selection[0] != '' ) {
                $classAttributeContent = $this->classAttributeContent( $objectAttribute->attribute( 'contentclass_attribute' ) );
                $availableOptions = $classAttributeContent['options'];
                foreach ( $availableOptions as $option ) {

                    if ( in_array( $option->attribute( 'identifier' ), $selection ) ) {
                        $nameArray[] = $option->name();
                    }
                    if ( $option->attribute( 'type' ) == OWEnhancedSelectionBasicOption::OPTGROUP_TYPE ) {
                        $subOptionList = $option->attribute( 'option_list' );
                        foreach ( $subOptionList as $subOption ) {
                            if ( in_array( $subOption->attribute( 'identifier' ), $selection ) ) {
                                $nameArray[] = $option->attribute( 'name' ) . '/' . $subOption->attribute( 'name' );
                            }
                        }
                    }
                }
            }
        }
        $delimiter = $classContent['delimiter'];
        if ( empty( $delimiter ) ) {
            $delimiter = $this->defaultDelimiter;
        }
        $dataText = join( $delimiter, $nameArray );
        $collectionAttribute->setAttribute( 'data_text', $dataText );
        return true;
    }

    function hasInformationCollection() {
        return false;
    }

    /*     * ********
     * GENERAL *
     * ******** */

    function metaData( $contentObjectAttribute ) {
        $content = $contentObjectAttribute->content();
        $metaDataArray = array();
        foreach ( $content['options'] as $option ) {
            if ( $option->attribute( 'optgroup' ) ) {
                $metaDataArray[] = array(
                    'optgroup' => $option->attribute( 'optgroup' )->attribute( 'identifier' ),
                    'optgroup_name' => $option->attribute( 'optgroup' )->attribute( 'name' )
                );
            }
            $metaDataArray[] = array(
                'option' => $option->attribute( 'identifier' ),
                'option_name' => $option->attribute( 'name' )
            );
        }
        return $metaDataArray;
    }

    function title( $contentObjectAttribute, $name = null ) {
        $content = $contentObjectAttribute->content();
        $classContent = $contentObjectAttribute->classContent();
        return $this->_title( $content, $classContent );
    }

    protected function _title( $content, $classContent ) {
        $titleArray = array();
        $titleString = "";
        if ( count( $content['options'] ) > 0 ) {
            $options = $content['options'];
            foreach ( $options as $option ) {
                $titleArray[] = ($option->attribute( 'optgroup' ) ? $option->attribute( 'optgroup' )->attribute( 'name' ) . '/' : "" ) . $option->attribute( 'name' );
            }
            unset( $options );
        }
        if ( count( $titleArray ) > 0 ) {
            $delimiter = $classContent['delimiter'];
            if ( empty( $delimiter ) ) {
                $delimiter = $this->defaultDelimiter;
            }
            $titleString = join( $delimiter, $titleArray );
        }
        return $titleString;
    }

    function isIndexable() {
        return true;
    }

    function isInformationCollector() {
        return true;
    }

    function sortKey( $objectAttribute ) {
        $content = $objectAttribute->content();
        $contentString = is_array( $content['identifiers'] ) ? strtolower( implode( ' ', $content['identifiers'] ) ) : "";

        return $contentString;
    }

    function sortKeyType() {
        return 'string';
    }

    /**
     * \param[in] $attribID The attribute ID used to make the custom action unique (class or object level)
     * \param[in] $http Instance of the eZHTTPTool class
     * \param[in] $action The name of the action if you want to check for a specific action
     * \retval boolean \c true if the custom action has fired; \c false if it hasn't
     * \brief Checks if a custom action ( combination of \a $attribID and \a $action ) has fired
     */
    function hasCustomAction( $attribID, $http, $action = false ) {
        if ( $http->hasPostVariable( 'CustomActionButton' ) ) {
            $keys = array_keys( $http->postVariable( 'CustomActionButton' ) );

            if ( $action !== false ) {
                $attribID .= "_$action";
            }

            foreach ( $keys as $key ) {
                if ( strpos( $key, "$attribID" ) === 0 ) { // Begins with the attribID
                    return true;
                }
            }
        }

        return false;
    }

    function isDbQueryValid( $sql ) {
        $db = eZDB::instance();
        if ( is_callable( 'eZDB::setErrorHandling' ) ) {
            eZDB::setErrorHandling( eZDB::ERROR_HANDLING_EXCEPTIONS );
        }
        try {
            $db->arrayQuery( $sql );
            if ( $db->ErrorNumber == 0 ) {
                return true;
            }
        } catch ( Exception $e ) {
            return false;
        }

        return false;
    }

    function getDbOptions( $classContent ) {
        $optionList = array();

        if ( isset( $classContent['query'] ) &&
            !empty( $classContent['query'] ) &&
            $this->isDbQueryValid( $classContent['query'] ) === true ) {
            $db = eZDB::instance();
            $res = $db->arrayQuery( $classContent['query'] );
            foreach ( $res as $res_item ) {
                $option = new OWEnhancedSelectionDBOption();
                $option->setAttribute( 'name', $res_item['name'] );
                $option->setAttribute( 'identifier', $res_item['identifier'] );
                $option->setAttribute( 'type', OWEnhancedSelectionBasicOption::OPTION_TYPE );
                if ( array_key_exists( 'g_identifier', $res_item ) ) {
                    if ( !isset( $optionList[$res_item['g_identifier']] ) ) {
                        $parentOption = new OWEnhancedSelectionDBOption();
                        $parentOption->setAttribute( 'name', $res_item['g_name'] );
                        $parentOption->setAttribute( 'identifier', $res_item['g_identifier'] );
                        $parentOption->setAttribute( 'type', OWEnhancedSelectionDBOption::OPTGROUP_TYPE );
                        $optionList[$res_item['g_identifier']] = $parentOption;
                    }
                    $parentOption = $optionList[$res_item['g_identifier']];
                    $parentSubOptionList = $parentOption->attribute( 'option_list' );
                    $option->setAttribute( 'optgroup', $parentOption );
                    $parentSubOptionList[] = $option;
                    $parentOption->setAttribute( 'option_list', $parentSubOptionList );
                } else {
                    $optionList[] = $option;
                }
            }
        }
        return array_values( $optionList );
    }

    function validateAttributeHTTPInput( $http, $base, $contentObjectAttribute, $isInformationCollection = false ) {
        $id = $contentObjectAttribute->attribute( 'id' );
        $classAttribute = $contentObjectAttribute->contentClassAttribute();
        $classContent = $classAttribute->content();
        $isRequired = false;
        $infoCollectionCheck = ( $isInformationCollection == $classAttribute->attribute( 'is_information_collector' ) );

        $isRequired = $contentObjectAttribute->validateIsRequired();

        $selectionName = join( '_', array( $base, 'owenhancedselection_selection', $id ) );
        if ( $http->hasPostVariable( $selectionName ) ) {
            $selection = $http->postVariable( $selectionName );

            if ( $infoCollectionCheck === true ) {
                switch ( true ) {
                    case $isRequired === true and count( $selection ) == 0:
                    case $isRequired === true and count( $selection ) == 1 and empty( $selection[0] ): {
                            $contentObjectAttribute->setValidationError( ezpI18n::tr( 'kernel/classes/datatypes', 'This is a required field.' ) );
                            return eZInputValidator::STATE_INVALID;
                        } break;
                }
            }
        } else {
            if ( $infoCollectionCheck === true and $isRequired === true and $classContent['is_multiselect'] == 1 ) {
                $contentObjectAttribute->setValidationError( ezpI18n::tr( 'kernel/classes/datatypes', 'This is a required field.' )
                );
            } else if ( $infoCollectionCheck === true and $isRequired === true ) {
                $contentObjectAttribute->setValidationError( ezpI18n::tr( 'kernel/classes/datatypes', 'No POST variable. Please check your configuration.' )
                );
            } else {
                return eZInputValidator::STATE_ACCEPTED;
            }

            return eZInputValidator::STATE_INVALID;
        }

        return eZInputValidator::STATE_ACCEPTED;
    }

    function fromString( $objectAttribute, $string ) {
        $content = array(
            'options' => array(),
            'identifiers' => unserialize( $string )
        );
        $objectAttribute->setContent( $content );
    }

    function toString( $objectAttribute ) {
        $content = $objectAttribute->content();
        return serialize( $content['identifiers'] );
    }

}

eZDataType::register( OWEnhancedSelectionType::DATATYPESTRING, "owenhancedselectiontype" );

