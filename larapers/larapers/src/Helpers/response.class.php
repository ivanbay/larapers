<?php

/**
 * Author: Ivan Paul Bay <ivan.bay@maximintegrated.com>
 * Date: May 23, 2016
 * File: larapers/src/Helpers/response.class.php
 */

namespace larapers\src\Helpers;

/**
 * Helper for conversion of return types
 * @author Ivan Paul Bay <ivan.bay@maximintegrated.com>
 */
class response {

    /**
     * Convert multidimensional or simple array into an object
     * @author Ivan Paul Bay <ivan.bay@maximintegrated.com>
     * @param array $arr
     * @return object
     */
    public static function object($arr) {

        if (is_array($arr)) {
            foreach( $arr as $k => $v ){
                $arr[$k] = self::object($v);
            }
            
            return (object)$arr;
        }
        
        return $arr;
    }
    
    /**
     * Convert an array to json format
     * @author Ivan Paul Bay <ivan.bay@maximintegrated.com>
     * @param array $arr
     * @return json
     */
    public static function json($arr) {

        if (is_array($arr)) {
            return json_encode($arr);
        } else {
            return $arr;
        }
    }

}
