<?php

class OWEnhancedSelectionSolrDocumentField extends ezfSolrDocumentFieldBase {

    const DEFAULT_ATTRIBUTE_TYPE = 'text';
    const DEFAULT_SUBATTRIBUTE_TYPE = 'mstring';

    public static function getFieldName( eZContentClassAttribute $classAttribute, $subAttribute = null, $context = 'search' ) {
        switch ( $classAttribute->attribute( 'data_type_string' ) ) {
            case 'owenhancedselection' : {
                    if ( $subAttribute and $subAttribute !== '' ) {
                        return parent::generateSubattributeFieldName( $classAttribute, $subAttribute, self::DEFAULT_SUBATTRIBUTE_TYPE );
                    } else {
                        return parent::generateAttributeFieldName( $classAttribute, self::getClassAttributeType( $classAttribute, null, $context ) );
                    }
                } break;

            default: {
                    
                } break;
        }
    }

    public function getData() {
        $contentClassAttribute = $this->ContentObjectAttribute->attribute( 'contentclass_attribute' );

        switch ( $contentClassAttribute->attribute( 'data_type_string' ) ) {
            case 'owenhancedselection' : {
                    $returnArray = array();
                    $value = $this->ContentObjectAttribute->metaData();

                    $fieldName = parent::generateAttributeFieldName( $contentClassAttribute, self::DEFAULT_ATTRIBUTE_TYPE );
                    $returnArray[$fieldName] = $this->ContentObjectAttribute->title();
                    $fieldOptgroupIdentifiersName = parent::generateSubattributeFieldName( $contentClassAttribute, 'optgroup_identifier', self::DEFAULT_SUBATTRIBUTE_TYPE );
                    $fieldOptgroupNamesName = parent::generateSubattributeFieldName( $contentClassAttribute, 'optgroup_name', self::DEFAULT_SUBATTRIBUTE_TYPE );
                    $fieldIdentifiersName = parent::generateSubattributeFieldName( $contentClassAttribute, 'identifier', self::DEFAULT_SUBATTRIBUTE_TYPE );
                    $fieldNamesName = parent::generateSubattributeFieldName( $contentClassAttribute, 'name', self::DEFAULT_SUBATTRIBUTE_TYPE );
                    $optgroupList = array();
                    $optgroupNameList = array();
                    $optionList = array();
                    $optionNameList = array();
                    foreach ( $value as $item ) {
                        if ( isset( $item['optgroup'] ) ) {
                            $optgroupList[] = $item['optgroup'];
                            $optgroupNameList[] = $item['optgroup_name'];
                        } else {
                            $optionList[] = $item['option'];
                            $optionNameList[] = $item['option_name'];
                        }
                    }
                    $returnArray[$fieldOptgroupIdentifiersName] = array_unique( $optgroupList );
                    $returnArray[$fieldOptgroupNamesName] = array_unique( $optgroupNameList );
                    $returnArray[$fieldIdentifiersName] = array_unique( $optionList );
                    $returnArray[$fieldNamesName] = array_unique( $optionNameList );
                    return $returnArray;
                } break;

            default: {
                    
                } break;
        }
    }

}
