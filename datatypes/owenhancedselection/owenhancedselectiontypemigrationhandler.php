<?php

class OWEnhancedSelectionTypeMigrationHandler extends DefaultDatatypeMigrationHandler {

    static public function toArray( eZContentClassAttribute $attribute ) {
        $content = $attribute->content();
        unset( $content['available_options'] );
        unset( $content['db_options'] );
        if ( $content['is_multiselect'] == false ) {
            unset( $content['is_multiselect'] );
        } else {
            $content['is_multiselect'] = true;
        }
        if ( empty( $content['delimiter'] ) ) {
            unset( $content['delimiter'] );
        }
        if ( empty( $content['query'] ) ) {
            unset( $content['query'] );
        }
        if ( !empty( $content['options'] ) ) {
            $optionList = array();
            foreach ( $content['options'] as $option ) {
                $optionArray = array(
                    'type' => $option->attribute( 'type' ),
                    'identifier' => $option->attribute( 'identifier' ),
                    'priority' => $option->attribute( 'priority' ),
                    'name' => OWMigrationTools::cleanupNameList( $option->attribute( 'nameList' ) )
                );
                if ( $option->attribute( 'has_option' ) ) {
                    $optionArray['option_list'] = array();
                    foreach ( $option->attribute( 'option_list' ) as $subOption ) {
                        $subOptionArray = array(
                            'type' => $subOption->attribute( 'type' ),
                            'identifier' => $subOption->attribute( 'identifier' ),
                            'priority' => $subOption->attribute( 'priority' ),
                            'name' => OWMigrationTools::cleanupNameList( $subOption->attribute( 'nameList' ) )
                        );
                        $optionArray['option_list'][] = $subOptionArray;
                    }
                }
                $optionList[] = $optionArray;
            }
            $content['options'] = $optionList;
        } else {
            unset( $content['options'] );
        }
        return $content;
    }

    static public function fromArray( eZContentClassAttribute $attribute, array $options ) {
        if ( array_key_exists( 'options', $options ) ) {
            foreach ( $options['options'] as $value ) {
                $selectSubOptionValueList = array();
                if ( array_key_exists( 'option_list', $value ) ) {
                    $selectSubOptionValueList = $value['option_list'];
                    unset( $value['option_list'] );
                }
                $selectOption = new OWEnhancedSelection( $value );
                $selectOption->setAttribute( 'contentclassattribute_id', $attribute->attribute( 'id' ) );
                if ( is_string( $value['name'] ) ) {
                    $selectOption->setName( $value['name'] );
                } elseif ( is_array( $value['name'] ) ) {
                    $nameList = new eZSerializedObjectNameList( serialize( $value['name'] ) );
                    $nameList->validate();
                    $selectOption->NameList = $nameList;
                }
                $selectOption->store();
                foreach ( $selectSubOptionValueList as $selectSubOptionValue ) {
                    $selectSubOption = new OWEnhancedSelection( $selectSubOptionValue );
                    $selectSubOption->setAttribute( 'optgroup_id', $selectOption->attribute( 'id' ) );
                    $selectSubOption->setAttribute( 'contentclassattribute_id', $attribute->attribute( 'id' ) );
                    if ( is_string( $selectSubOptionValue['name'] ) ) {
                        $selectSubOption->setName( $selectSubOptionValue['name'] );
                    } elseif ( is_array( $selectSubOptionValue['name'] ) ) {
                        $nameList = new eZSerializedObjectNameList( serialize( $selectSubOptionValue['name'] ) );
                        $nameList->validate();
                        $selectSubOption->NameList = $nameList;
                    }
                    $selectSubOption->store();
                }
            }
            unset( $options['options'] );
        }
        $content = $attribute->content();
        $content = array_merge( $content, $options );
        $attribute->setContent( $content );
    }

}
