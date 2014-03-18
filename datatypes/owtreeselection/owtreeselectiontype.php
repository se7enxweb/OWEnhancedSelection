<?php

/*
  Tree selection extension for eZ publish 4.x
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
  \class   OWTreeSelectionType owtreeselectiontype.php
  \ingroup eZDatatype
  \brief   Handles the datatype owtreeselection.
  \version 3.0
  \date    Tuesday 16 August 2005 9:56:00 am
  \author  Madeline Veyrenc
 */

class OWTreeSelectionType extends eZDataType {

    const DATATYPESTRING = 'owtreeselection';
    const CONTENT_CLASS_STORAGE = 'data_text5';

    function __construct() {
        $this->eZDataType( self::DATATYPESTRING, ezpI18n::tr( 'kernel/classes/datatypes', 'Tree selection', 'Datatype name' ), array(
            'serialize_supported' => true,
            'object_serialize_map' => array( 'data_text' => 'selection' )
        ) );
    }

    /*     * ******
     * CLASS *
     * ****** */

    function validateClassAttributeHTTPInput( $http, $base, $classAttribute ) {
        $id = $classAttribute->attribute( 'id' );
        $queryName = join( '_', array( $base, 'owtreeselection_query', $id ) );

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

        $idArrayName = "{$base}_owtreeselection_id_{$id}";
        $nameArrayName = "{$base}_owtreeselection_name_{$id}";
        $identifierArrayName = "{$base}_owtreeselection_identifier_{$id}";
        $priorityArrayName = "{$base}_owtreeselection_priority_{$id}";
        $typeArrayName = "{$base}_owtreeselection_type_{$id}";

        $multiSelectName = "{$base}_owtreeselection_multi_{$id}";
        $delimiterName = "{$base}_owtreeselection_delimiter_{$id}";

        $queryName = "{$base}_owtreeselection_query_{$id}";

        if ( $http->hasPostVariable( $idArrayName ) ) {
            $idArray = $http->postVariable( $idArrayName );
            $nameArray = $http->postVariable( $nameArrayName );
            $identifierArray = $http->postVariable( $identifierArrayName );
            $priorityArray = $http->postVariable( $priorityArrayName );
            $typeArray = $http->postVariable( $typeArrayName );
            foreach ( $idArray as $id ) {
                $name = isset( $nameArray[$id] ) ? $nameArray[$id] : '';
                $identifier = isset( $identifierArray[$id] ) && !empty( $identifierArray[$id] ) ? $identifierArray[$id] : $this->generateIdentifier( $name, $identifierArray );
                $priority = isset( $priorityArray[$id] ) ? $priorityArray[$id] : 1;

                $option = array(
                    'id' => $id,
                    'name' => $name,
                    'identifier' => $identifier,
                    'priority' => $priority );
                if ( isset( $typeArray[$id] ) && $typeArray[$id] == 'group' ) {
                    $option['options'] = array();
                }
                foreach ( $content['options'] as $index1 => $option1 ) {
                    if ( $option1['id'] == $id ) {
                        if ( isset( $option1['options'] ) ) {
                            $option['options'] = $option1['options'];
                        }
                        $content['options'][$index1] = $option;
                        continue;
                    } elseif ( isset( $option1['options'] ) ) {
                        foreach ( $option1['options'] as $index2 => $option2 ) {
                            if ( $option2['id'] == $id ) {
                                $content['options'][$index1]['options'][$index2] = $option;
                                continue;
                            }
                        }
                    }
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
        /*
          $xmlString = $classAttribute->attribute( self::CONTENT_CLASS_STORAGE );
          $content = array();

          $this->xmlToClassContent( $xmlString, $content );

          $content['db_options'] = $this->getDbOptions( $content );

          $queryName = join( '_', array( 'ContentClass_owtreeselection_query', $classAttribute->attribute( 'id' ) ) );
          $http = eZHTTPTool::instance();

          if ( empty( $content['query'] ) and
          $http->hasPostVariable( $queryName ) ) {
          $query = $http->postVariable( $queryName );
          $content['query'] = $query;
          }
         */
        $content = @unserialize( $classAttribute->attribute( self::CONTENT_CLASS_STORAGE ) );
        if ( empty( $content ) ) {
            $content = array(
                'options' => array(),
                'is_multiselect' => 0,
                'delimiter' => '',
                'query' => '',
                'db_options' => null
            );
        } else {
            $content['db_options'] = $this->getDbOptions( $content );
        }
        return $content;
    }

    function storeClassAttribute( $classAttribute, $version ) {
        $content = $classAttribute->content();
        unset( $content['db_options'] ); // Make sure this can never slip into the database
        /* $xmlString = $this->classContentToXml( $content ); */
        $classAttribute->setAttribute( self::CONTENT_CLASS_STORAGE, serialize( $content ) );
    }

    function customClassAttributeHTTPAction( $http, $action, $classAttribute ) {
        $id = $classAttribute->attribute( 'id' );
        $base = "ContentClass";
        $content = $classAttribute->content();

        $idArrayName = "{$base}_owtreeselection_id_{$id}";
        $idArray = array();

        if ( $http->hasPostVariable( $idArrayName ) ) {
            $idArray = $http->postVariable( $idArrayName );
        }

        $actionlist = explode( "_", $action );
        $processAction = $actionlist[0];
        switch ( $processAction ) {
            case 'new-option-group':
                $nextID = $this->getOptionNextId( $content );
                $content['options'][] = array( 'id' => $nextID,
                    'name' => '',
                    'identifier' => '',
                    'options' => array(),
                    'priority' => 1 );
                break;
            case 'new-option':
                $nextID = $this->getOptionNextId( $content );
                if ( isset( $actionlist[1] ) ) {
                    foreach ( $content['options'] as $index => $option ) {
                        if ( $option['id'] == $actionlist[1] ) {
                            $content['options'][$index]['options'][] = array( 'id' => $nextID,
                                'name' => '',
                                'identifier' => '',
                                'priority' => 1 );
                        }
                    }
                } else {
                    $content['options'][] = array( 'id' => $nextID,
                        'name' => '',
                        'identifier' => '',
                        'priority' => 1 );
                }
                break;

            case 'remove-selected-option':
                $removeArrayName = "{$base}_owtreeselection_remove_{$id}";

                if ( $http->hasPostVariable( $removeArrayName ) ) {
                    $removeArray = $http->postVariable( $removeArrayName );

                    foreach ( $removeArray as $removeID ) {
                        foreach ( $content['options'] as $index => $option ) {
                            if ( $option['id'] == $removeID ) {
                                unset( $content['options'][$index] );
                                $content['options'] = array_values( $content['options'] );
                                continue;
                            } elseif ( isset( $option['options'] ) ) {
                                foreach ( $option['options'] as $subIndex => $subOption ) {
                                    if ( $subOption['id'] == $removeID ) {
                                        unset( $content['options'][$index]['options'][$subIndex] );
                                        $content['options'][$index]['options'] = array_values( $content['options'][$index]['options'] );
                                        continue;
                                    }
                                }
                            }
                        }
                    }
                }
                break;

            case 'move-up':
                if ( isset( $actionlist[1] ) && isset( $actionlist[2] ) ) {
                    $this->swapRows( $actionlist[1], $actionlist[2], $content );
                }
                break;

            case 'move-down':
                if ( isset( $actionlist[1] ) && isset( $actionlist[2] ) ) {
                    $this->swapRows( $actionlist[1], $actionlist[2], $content );
                }
                break;

            case 'sort-option-group':
                $sortName = "{$base}_owtreeselection_sort_order_{$id}";
                if ( $http->hasPostVariable( $sortName ) ) {
                    $sort = $http->postVariable( $sortName );

                    if ( strpos( $sort, '_' ) !== false ) {
                        list( $type, $ranking ) = explode( '_', $sort );



// Use POST priorities instead of the stored ones
// Otherwise you have to store new priorities before you can sort
                        $priorityArray = array();
                        if ( $type == 'prior' ) {
                            $priorityArray = $http->postVariable( "{$base}_owtreeselection_priority_{$id}" );
                        }

                        $content['options'] = $this->sortOptions( $content['options'], $type, $ranking, $priorityArray );
                    } else {
                        eZDebug::writeError( "Unknown sort value. Please use the form type_order (ex. alpha_asc)", "OWTreeSelectionType" );
                    }
                }
                break;

            default:
                eZDebug::writeError( "Unknown class HTTP action: $action", "OWTreeSelectionType" );
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

        $selectionName = join( '_', array( $base, 'owtreeselection_selection', $id ) );

        if ( $http->hasPostVariable( $selectionName ) ) {
            $selection = $http->postVariable( $selectionName );

            $content = $selection;
        } else if ( $classContent['is_multiselect'] == 1 ) {
            $content = array();
        }

        $contentObjectAttribute->setContent( $content );

        return true;
    }

    function objectAttributeContent( $contentObjectAttribute ) {
        $content = array();
        $contentString = $contentObjectAttribute->attribute( 'data_text' );

        if ( !empty( $contentString ) ) {
            $content = unserialize( $contentString );
        }

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

        $contentString = serialize( $content );

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
        $content = $objectAttribute->content();
        $nameArray = array();

        $selectionName = join( '_', array( $base, 'owtreeselection_selection', $id ) );
        $selection = $http->postVariable( $selectionName );

        if ( $http->hasPostVariable( $selectionName ) ) {
            $selection = $http->postVariable( $selectionName );

            if ( count( $selection ) > 0 ) {
                $options = $classContent['options'];

                if ( isset( $classContent['db_options'] ) and count( $classContent['db_options'] ) > 0 ) {
                    unset( $options );
                    $options = $classContent['db_options'];
                }

                foreach ( $options as $option ) {
                    if ( in_array( $option['identifier'], $selection ) ) {
                        $nameArray[] = $option['name'];
                    }
                }

                unset( $options );
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
                if ( in_array( $option['identifier'], $content ) ) {
                    $metaDataArray[] = array( 'id' => '',
                        'text' => $option['identifier'] );
                    $metaDataArray[] = array( 'id' => '',
                        'text' => $option['name'] );
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

    function swapRows( $optionID1, $optionID2, &$content ) {
        foreach ( $content['options'] as $index1 => $option1 ) {
            if ( $option1['id'] == $optionID1 ) {
                $tmpOption1 = $option1;
                foreach ( $content['options'] as $index2 => $option2 ) {
                    if ( $option2['id'] == $optionID2 ) {
                        $content['options'][$index1] = $option2;
                        $content['options'][$index2] = $tmpOption1;
                        continue;
                    }
                }
                continue;
            } elseif ( isset( $option1['options'] ) ) {
                foreach ( $option1['options'] as $subIndex1 => $subOption1 ) {
                    if ( $subOption1['id'] == $optionID1 ) {
                        $tmpOption1 = $subOption1;
                        foreach ( $option1['options'] as $subIndex2 => $subOption2 ) {
                            if ( $subOption2['id'] == $optionID2 ) {
                                $content['options'][$index1]['options'][$subIndex1] = $option2;
                                $content['options'][$index1]['options'][$subIndex2] = $tmpOption1;
                                continue;
                            }
                        }
                        continue;
                    }
                }
            }
        }
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

        $selectionName = join( '_', array( $base, 'owtreeselection_selection', $id ) );

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

    function getOptionNextId( $content ) {
        $maxID = 0;
        foreach ( $content['options'] as $option ) {
            if ( intval( $option['id'] ) > $maxID ) {
                $maxID = intval( $option['id'] );
            }
            if ( isset( $option['options'] ) ) {
                foreach ( $option['options'] as $subOption ) {
                    if ( intval( $subOption['id'] ) > $maxID ) {
                        $maxID = intval( $subOption['id'] );
                    }
                }
            }
        }
        return ++$maxID;
    }

    function sortOptions( $currentOptions, $type, $ranking, $priorityArray = array() ) {
        $sortArray = array();
        $sortOrder = SORT_ASC;
        $sortType = SORT_STRING;
        $numericSorts = array( 'prior' );
        switch ( $ranking ) {
            case 'desc':
                $sortOrder = SORT_DESC;
                break;

            case 'asc':
            default:
                $sortOrder = SORT_ASC;
                break;
        }

        if ( in_array( $type, $numericSorts ) ) {
            $sortType = SORT_NUMERIC;
        }
        foreach ( array_keys( $currentOptions ) as $key ) {
            $option = $currentOptions[$key];

            switch ( $type ) {
                case 'prior':
                    if ( isset( $priorityArray[$option['id']] ) ) {
                        $option['priority'] = $priorityArray[$option['id']];
                    }
                    $sortArray[] = $option['priority'];
                    break;

                case 'alpha':
                default:
                    $sortArray[] = $option['name'];
                    break;
            }

            unset( $option );
        }
        array_multisort( $sortArray, $sortOrder, $sortType, $currentOptions );
        foreach ( $currentOptions as $index => $options ) {
            if ( isset( $options['options'] ) ) {
                $currentOptions[$index]['options'] = $this->sortOptions( $options['options'], $type, $ranking, $priorityArray );
            }
        }
        return $currentOptions;
    }

}

eZDataType::register( OWTreeSelectionType::DATATYPESTRING, "owtreeselectiontype" );
