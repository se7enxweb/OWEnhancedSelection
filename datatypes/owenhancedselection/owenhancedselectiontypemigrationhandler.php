<?php

class OWEnhancedSelectionTypeMigrationHandler extends DefaultDatatypeMigrationHandler {

    static public function toArray( eZContentClassAttribute $attribute ) {
        $content = $attribute->content();
        unset( $content['options'] );
        unset( $content['db_options'] );
        unset( $content['options_by_identifier'] );
        unset( $content['available_options'] );
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
        if ( empty( $content['is_deserialized'] ) ) {
            unset( $content['is_deserialized'] );
        }
        if ( !empty( $content['basic_options'] ) ) {
            $optionList = array();
            foreach ( $content['basic_options'] as $option ) {
                $optionArray = array(
                    'identifier' => $option->attribute( 'identifier' ),
                    'priority' => $option->attribute( 'priority' ),
                    'name' => OWMigrationTools::cleanupNameList( $option->attribute( 'nameList' ) )
                );
                if ( $option->attribute( 'has_option' ) ) {
                    $optionArray['option_list'] = array();
                    foreach ( $option->attribute( 'option_list' ) as $subOption ) {
                        $subOptionArray = array(
                            'identifier' => $subOption->attribute( 'identifier' ),
                            'priority' => $subOption->attribute( 'priority' ),
                            'name' => OWMigrationTools::cleanupNameList( $subOption->attribute( 'nameList' ) )
                        );
                        $optionArray['option_list'][] = $subOptionArray;
                    }
                } elseif ( $option->attribute( 'type' ) == OWEnhancedSelectionBasicOption::OPTGROUP_TYPE ) {
                    $optionArray['type'] = $option->attribute( 'type' );
                }
                $optionList[] = $optionArray;
            }
            $content['options'] = $optionList;
        }
        unset( $content['basic_options'] );
        return $content;
    }

    static public function fromArray( eZContentClassAttribute $attribute, array $options ) {
        if ( array_key_exists( 'options', $options ) ) {
            foreach ( $options['options'] as $value ) {
                $selectSubOptionValueList = array();
                if ( array_key_exists( 'option_list', $value ) ) {
                    $selectSubOptionValueList = $value['option_list'];
                    $value['type'] = OWEnhancedSelectionBasicOption::OPTGROUP_TYPE;
                    unset( $value['option_list'] );
                }
                $value['contentclassattribute_id'] = $attribute->attribute( 'id' );
                $selectOption = OWEnhancedSelectionBasicOption::createOrUpdate( $value );
                if ( is_string( $value['name'] ) ) {
                    $selectOption->setName( $value['name'] );
                } elseif ( is_array( $value['name'] ) ) {
                    $nameList = new eZSerializedObjectNameList( serialize( $value['name'] ) );
                    $nameList->validate();
                    $selectOption->NameList = $nameList;
                }
                $selectOption->store();
                foreach ( $selectSubOptionValueList as $selectSubOptionValue ) {
                    $selectSubOptionValue['contentclassattribute_id'] = $attribute->attribute( 'id' );
                    $selectSubOption = OWEnhancedSelectionBasicOption::createOrUpdate( $selectSubOptionValue );
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
