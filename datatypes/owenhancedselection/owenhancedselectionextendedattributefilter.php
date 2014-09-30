<?php

class OWEnhancedSelectionExtendedAttributeFilter {
    /*
     * In this method is where we are going to modify different parts of the SQL query being passed to MySQL
     * 
     * $params gets the information passed from the fetch function
     * and we must return an array with those modifications
     * array(
     *     'tables'    => '<TABLES to use>'
     *     , 'joins'   => '<WHERE filtering> '
     *     , 'columns' => '<COLUMNS to return>'
     * )
     */

    static function createSqlParts( $params ) {
        $tablesList = array();
        $joinsList = array();
        foreach ( $params as $paramItem ) {
            if ( array_key_exists( 'attribute', $paramItem ) && array_key_exists( 'values', $paramItem ) ) {
                if ( !array_key_exists( 'filter', $paramItem ) ) {
                    $paramItem['filter'] = 'in';
                } else {
                    $paramItem['filter'] = strtolower( $paramItem['filter'] );
                }
                if ( !is_numeric( $paramItem['attribute'] ) ) {
                    $paramItem['attribute'] = eZContentObjectTreeNode::classAttributeIDByIdentifier( $paramItem['attribute'] );
                }
                if ( !is_array( $paramItem['values'] ) ) {
                    $paramItem['values'] = array( $paramItem['values'] );
                }
                $tablesList[] = 'INNER JOIN ezcontentobject_attribute AS ezcontentobject_attribute_' . $paramItem['attribute'] . ' ON ( ezcontentobject.id = ezcontentobject_attribute_' . $paramItem['attribute'] . '.contentobject_id AND ezcontentobject_attribute_' . $paramItem['attribute'] . '.contentclassattribute_id = ' . $paramItem['attribute'] . ' )';
                $joinBaseString = "( ezcontentobject_attribute_" . $paramItem['attribute'] . ".sort_key_string %s %s )";
                switch ( $paramItem['filter'] ) {
                    case '=':
                        $regexp = '"^((' . implode( '|', $paramItem['values'] ) . '){1}[[. .]]?)+$"';
                        $joinsList[] = sprintf( $joinBaseString, 'REGEXP', $regexp );
                        break;
                    case '!=':
                        $regexp = '"^((' . implode( '|', $paramItem['values'] ) . '){1}[[. .]]?)+$"';
                        $joinsList[] = sprintf( $joinBaseString, 'NOT REGEXP', $regexp );
                        break;
                    case 'in':
                        $regexp = '"[[:<:]](' . implode( '|', $paramItem['values'] ) . ')[[:>:]]"';
                        $joinsList[] = sprintf( $joinBaseString, 'REGEXP', $regexp );
                        break;
                    case 'not in':
                        $regexp = '"[[:<:]](' . implode( '|', $paramItem['values'] ) . ')[[:>:]]"';
                        $joinsList[] = sprintf( $joinBaseString, 'NOT REGEXP', $regexp );
                        break;
                    case 'regexp':
                        $regexp = '"' . implode( '|', $paramItem['values'] ) . '"';
                        $joinsList[] = sprintf( $joinBaseString, 'REGEXP', $regexp );
                        break;
                    default:
                        eZDebug::writeError( "Bad filter '" . $paramItem['filter'] . "'.", 'extended_attribute_filter::enhancedselection' );
                }
            } else {
                eZDebug::writeError( "Missing 'attribute' or 'values' in params.", 'extended_attribute_filter::enhancedselection' );
            }
        }
        $result = array(
            'tables' => implode( ' ', $tablesList ),
            'joins' => implode( ' AND ', $joinsList ) . ' AND '
        );
        return $result;
    }

}
