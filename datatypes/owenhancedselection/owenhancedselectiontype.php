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

    function __construct() {
        $this->eZDataType( self::DATATYPESTRING, ezpI18n::tr( 'kernel/classes/datatypes', 'Enhanced selection (OW)', 'Datatype name' ), array(
            'serialize_supported' => true,
            'object_serialize_map' => array( 'data_text' => 'selection' )
        ) );
    }

    /*     * ******
     * CLASS *
     * ****** */

    function validateClassAttributeHTTPInput( $http, $base, $classAttribute ) {
        $id = $classAttribute->attribute( 'id' );
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

        if ( $http->hasPostVariable( $idArrayName ) ) {
            $idArray = $http->postVariable( $idArrayName );
            $nameArray = $http->postVariable( $nameArrayName );
            $identifierArray = $http->postVariable( $identifierArrayName );
            foreach ( $idArray as $id ) {
                $name = isset( $nameArray[$id] ) ? $nameArray[$id] : '';
                $identifier = isset( $identifierArray[$id] ) && !empty( $identifierArray[$id] ) ? $identifierArray[$id] : $this->generateIdentifier( $name, $identifierArray );
                $option = OWEnhancedSelection::fetch( array( 'id' => $id ) );
                if ( $option instanceof OWEnhancedSelection ) {
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
        }

        $classAttribute->setContent( $content );
        $classAttribute->store();

        return true;
    }

    function classAttributeContent( $classAttribute ) {
        $content = @unserialize( $classAttribute->attribute( self::CONTENT_CLASS_STORAGE ) );
        if ( empty( $content ) ) {
            $content = array(
                'options' => array(),
                'is_multiselect' => 0,
                'delimiter' => '',
                'query' => '',
                'db_options' => array(),
                'available_options' => array()
            );
        } else {
            $content['options'] = OWEnhancedSelection::fetchAttributeOptionlist( $classAttribute->attribute( 'id' ) );
            $content['db_options'] = $this->getDbOptions( $content );
            $content['available_options'] = empty( $content['db_options'] ) ? $content['options'] : $content['db_options'];
        }
        return $content;
    }

    function storeClassAttribute( $classAttribute, $version ) {
        $content = $classAttribute->content();
        unset( $content['db_options'] );
        unset( $content['options'] );
        $classAttribute->setAttribute( self::CONTENT_CLASS_STORAGE, serialize( $content ) );
    }

    function deleteStoredClassAttribute( $classAttribute, $version = null ) {
        $content = $classAttribute->content();
        foreach ( $content['options'] as $option ) {
            $option->remove();
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
                    'contentclassattribute_id' => $classAttribute->attribute( 'id' ),
                    'name' => '',
                    'identifier' => '',
                    'type' => OWEnhancedSelection::OPTGROUP_TYPE
                );
                $option = new OWEnhancedSelection( $row );
                $option->store();
                break;
            case 'new-option':
                $row = array(
                    'contentclassattribute_id' => $classAttribute->attribute( 'id' ),
                    'name' => '',
                    'identifier' => '',
                    'type' => OWEnhancedSelection::OPTION_TYPE
                );
                if ( isset( $actionlist[1] ) ) {
                    $row['optgroup_id'] = $actionlist[1];
                }
                $option = new OWEnhancedSelection( $row );
                $option->store();
                break;

            case 'remove-selected-option':
                $removeArrayName = "{$base}_owenhancedselection_remove_{$id}";

                if ( $http->hasPostVariable( $removeArrayName ) ) {
                    $removeArray = $http->postVariable( $removeArrayName );

                    foreach ( $removeArray as $removeID ) {
                        $option = OWEnhancedSelection::fetch( array( 'id' => $removeID ) );
                        if ( $option instanceof OWEnhancedSelection ) {
                            $option->remove();
                        }
                    }
                }
                break;

            case 'move-up':
                if ( isset( $actionlist[1] ) && isset( $actionlist[2] ) ) {
                    OWEnhancedSelection::swapOptions( $actionlist[1], $actionlist[2] );
                }
                break;

            case 'move-down':
                if ( isset( $actionlist[1] ) && isset( $actionlist[2] ) ) {
                    OWEnhancedSelection::swapOptions( $actionlist[1], $actionlist[2] );
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
        OWEnhancedSelection::$localeCode = $contentObjectAttribute->attribute( 'language_code' );

        $optionList = array();
        $identifierList = array();
        $contentString = $contentObjectAttribute->attribute( 'data_text' );
        if ( !empty( $contentString ) ) {
            $identifierList = unserialize( $contentString );
        }
        $classAttributeContent = $this->classAttributeContent( $contentObjectAttribute->attribute( 'contentclass_attribute' ) );
        $availableOptions = $classAttributeContent['available_options'];
        foreach ( $availableOptions as $option ) {
            $optionArray = (array) $option;
            if ( in_array( $optionArray['identifier'], $identifierList ) ) {
                $optionList[] = $option;
            }
            if ( $optionArray['type'] == OWEnhancedSelection::OPTGROUP_TYPE ) {
                $subOptionList = $option instanceof OWEnhancedSelection ? $option->attribute( 'option_list' ) : $option['option_list'];
                foreach ( $subOptionList as $subOption ) {
                    $subOptionArray = (array) $subOption;
                    if ( in_array( $subOptionArray['identifier'], $identifierList ) ) {
                        if ( is_array( $subOption ) ) {
                            $subOption['optgroup'] = $option;
                        }
                        $optionList[] = $subOption;
                    }
                }
            }
        }
        $content = array(
            'options' => $optionList,
            'identifiers' => $identifierList
        );
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

            if ( count( $selection ) > 0 ) {
                $classAttributeContent = $this->classAttributeContent( $objectAttribute->attribute( 'contentclass_attribute' ) );
                $availableOptions = $classAttributeContent['available_options'];
                foreach ( $availableOptions as $option ) {
                    $optionArray = (array) $option;
                    if ( in_array( $optionArray['identifier'], $selection ) ) {
                        $nameArray[] = $option['name'];
                    }
                    if ( $optionArray['type'] == OWEnhancedSelection::OPTGROUP_TYPE ) {
                        $subOptionList = $option instanceof OWEnhancedSelection ? $option->attribute( 'option_list' ) : $option['option_list'];
                        foreach ( $subOptionList as $subOption ) {
                            $subOptionArray = (array) $subOption;
                            if ( in_array( $subOptionArray['identifier'], $selection ) ) {
                                $nameArray[] = $option['name'] . '/' . $subOption['name'];
                            }
                        }
                    }
                }
            }
        }

        $delimiter = $classContent['delimiter'];

        if ( empty( $delimiter ) ) {
            $delimiter = ', ';
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
        $classContent = $contentObjectAttribute->classContent();

        if ( count( $content ) > 0 ) {
            $metaDataArray = array();
            $options = $classContent['options'];

            if ( isset( $classContent['db_options'] ) and count( $classContent['db_options'] ) > 0 ) {
                unset( $options );
                $options = $classContent['db_options'];
            }

            foreach ( $options as $option ) {
                if ( $option instanceof OWEnhancedSelection ) {
                    $identifier = $option->attribute( 'identifier' );
                    $name = $option->attribute( 'name' );
                } else {
                    $identifier = $option['identifier'];
                    $name = $option['name'];
                }
                if ( in_array( $identifier, $content ) ) {
                    $metaDataArray[] = array( 'id' => '',
                        'text' => $identifier );
                    $metaDataArray[] = array( 'id' => '',
                        'text' => $name );
                }
            }

            unset( $options );

            return $metaDataArray;
        }

        return "";
    }

    function title( $contentObjectAttribute, $name = null ) {
        $content = $contentObjectAttribute->content();
        $classContent = $contentObjectAttribute->classContent();
        $titleArray = array();
        $titleString = "";

        if ( count( $content ) > 0 ) {
            $options = $classContent['options'];

            if ( isset( $classContent['db_options'] ) and count( $classContent['db_options'] ) > 0 ) {
                unset( $options );
                $options = $classContent['db_options'];
            }

            foreach ( $options as $option ) {
                if ( in_array( $option['identifier'], $content ) ) {
                    $titleArray[] = $option['name'];
                }
            }

            unset( $options );
        }

        if ( count( $titleArray ) > 0 ) {
            $delimiter = $classContent['delimiter'];

            if ( empty( $delimiter ) ) {
                $delimiter = ", ";
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
        $contentString = join( ' ', $content );
        $contentString = strtolower( $contentString );

        return $contentString;
    }

    function sortKeyType() {
        return 'string';
    }

    function generateIdentifier( $name, $identifierArray = array() ) {
        if ( empty( $name ) ) {
            return '';
        }

        $identifier = $name;

        $trans = eZCharTransform::instance();
        $generatedIdentifier = $trans->transformByGroup( $identifier, 'identifier' );


// We have $generatedIdentifier now, check for existance
        if ( is_array( $identifierArray ) and
                count( $identifierArray ) > 0 and
                in_array( $generatedIdentifier, $identifierArray ) ) {
            $highestNumber = 0;

            foreach ( $identifierArray as $ident ) {
                if ( preg_match( '/^' . $generatedIdentifier . '__(\d+)$/', $ident, $matchArray ) ) {
                    if ( $matchArray[1] > $highestNumber ) {
                        $highestNumber = $matchArray[1];
                    }
                }
            }

            $generatedIdentifier .= "__" . ++$highestNumber;
        }

        return $generatedIdentifier;
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
        eZDB::setErrorHandling( eZDB::ERROR_HANDLING_EXCEPTIONS );
        try {
            $res = $db->arrayQuery( $sql, array( 'limit' => 1 ) );
            if ( $db->ErrorNumber == 0 ) {
                return true;
            }
        } catch ( Exception $e ) {
            return false;
        }

        return false;
    }

    function getDbOptions( $classContent ) {
        $ret = array();

        if ( isset( $classContent['query'] ) &&
                !empty( $classContent['query'] ) &&
                $this->isDbQueryValid( $classContent['query'] ) === true ) {
            $db = eZDB::instance();
            $res = $db->arrayQuery( $classContent['query'] );
            $firstRes = current( $res );
            if ( isset( $firstRes['g_identifier'] ) && isset( $firstRes['g_name'] ) ) {
                $newRes = array();
                foreach ( $res as $res_item ) {
                    if ( !isset( $newRes[$res_item['g_identifier']] ) ) {
                        $newRes[$res_item['g_identifier']] = array(
                            'name' => $res_item['g_name'],
                            'identifier' => $res_item['g_identifier'],
                            'type' => OWEnhancedSelection::OPTGROUP_TYPE,
                            'option_list' => array()
                        );
                    }
                    $newRes[$res_item['g_identifier']]['option_list'][] = array(
                        'name' => $res_item['name'],
                        'identifier' => $res_item['identifier'],
                    );
                }
                $res = $newRes;
            }
            if ( is_array( $res ) and count( $res ) > 0 ) {
                if ( $classContent['is_multiselect'] == 0 ) {
                    $ret = array_merge( array( array( 'name' => '', 'identifier' => '' ) ), $res );
                } else {
                    $ret = $res;
                }
            }
        }

        return $ret;
    }

    function validateAttributeHTTPInput( $http, $base, $contentObjectAttribute, $isInformationCollection = false ) {
        $id = $contentObjectAttribute->attribute( 'id' );
        $classAttribute = $contentObjectAttribute->contentClassAttribute();
        $classContent = $classAttribute->content();
        $isRequired = false;
        $infoCollectionCheck = ( $isInformationCollection == $classAttribute->attribute( 'is_information_collector' ) );

        $isRequired = $contentObjectAttribute->validateIsRequired();

        $selectionName = join( '_', array( $base, 'owenhancedselection_selection',
            $id ) );

        if ( $http->hasPostVariable( $selectionName ) ) {
            $selection = $http->postVariable( $selectionName );

            if ( $infoCollectionCheck === true ) {
                switch ( true ) {
                    case $isRequired === true and count( $selection ) == 0:
                    case $isRequired === true and count( $selection ) == 1 and empty( $selection[0] ): {
                            $contentObjectAttribute->setValidationError( ezpI18n::tr( 'kernel/classes/datatypes', 'This is a required field.' )
                            );
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
        $content = unserialize( $string );
        $objectAttribute->setContent( $content );
    }

    function toString( $objectAttribute ) {
        $content = $objectAttribute->content();
        return serialize( $content['identifiers'] );
    }

}

eZDataType::register( OWEnhancedSelectionType::DATATYPESTRING, "owenhancedselectiontype" );
