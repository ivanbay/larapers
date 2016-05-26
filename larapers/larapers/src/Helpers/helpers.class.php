<?php

/**
 * Author: Ivan Paul Bay <ivan.bay@maximintegrated.com>
 * Date: May 23, 2016
 * File: larapers/src/Helpers/helpers.class.php
 */

namespace larapers\src\Helpers;

/**
 * Helpers that will make your development very easy
 * 
 * @author Ivan Paul Bay <ivan.bay@maximintegrated.com>
 * @package larapers\libs
 */
class helpers {

    /**
     * Paths.php file location
     * @var string $path_dir
     * @access private static 
     */
    private static $path_dir = "/bootstrap/paths.php";

    /**
     * Method that will check if array key is integer
     * @author Ivan Paul Bay <ivan.bay@maximintegrated.com>
     * @access public
     * @static
     * @param array $arr - array to check
     * @return bolean
     */
    public static function is_arr_key_int($arr) {
        foreach (array_keys($arr) as $a) {
            if (!is_int($a)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Method to get config from configuration file.
     * @author Ivan Paul Bay <ivan.bay@maximintegrated.com>
     * @access public static
     * @param $config Config to get [config file].[config key]
     * @example database.hostname This will get the database hostname from database file
     * @return string Config value
     */
    public static function config_get($config) {
        if (strpos($config, ".") !== false) {
            $exConfig = explode(".", $config);
            $file = $exConfig[0];
            $key = $exConfig[1];

            $configs = include self::path_get("config") . "/" . $file . ".php";
            $config = &$configs;

            if (strpos($key, "|") !== false) {
                
                $keys = explode("|", rtrim(ltrim($key, "["), "]"));
                foreach ($keys as $index) {
                    $config = &$config[$index];
                }

                return $config;
            } else {
                return $config[$key];
            }
        }
    }

    /**
     * Method to get path
     * @author Ivan Paul Bay <ivan.bay@maximintegrated.com>
     * @access public static
     * @param string $path Path name to get
     * @example base This will return the base path
     * @example log This will return the log path
     * @example config This will return the config path
     */
    public static function path_get($path) {

        $config = include __DIR__ . "/../.." . self::$path_dir;
        return $config[$path];
    }
    
    public static function arraytoobj($arr){
        $obj = new stdClass();
        
        if ( is_array($arr) ){
            
        } 
        
    }

}
