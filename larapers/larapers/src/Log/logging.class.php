<?php

namespace larapers\src\Log;

use larapers\src\Helpers\helpers;
/**
 *
 * @package 
 * @author 
 * */
class logging {

    public static function errorHandler($errno, $errstr, $errfile, $errline) {
        self::error($errno . " " . $errstr . " " . $errfile . " " . $errline);
        throw new Exception($errno . " + " . $errstr . " + " . $errfile . " + " . $errline);
    }

    public static function error($logmess) {
        
        if (!file_exists(helpers::path_get("log"))) {
            mkdir(helpers::path_get("log"), 0777, true);
        }
        $file = helpers::path_get("log") . "errorlogs" . ".txt";
        $logs = date('[d/M/Y h:i:s a] | ') . " " . $logmess . PHP_EOL;
        file_put_contents($file, $logs, FILE_APPEND | LOCK_EX);
    }

    public static function message($logmess, $transactional = false, $filename = null, $folder = null) {
        if (!file_exists(helpers::path_get("log"))) {
            mkdir(helpers::path_get("log"), 0777, true);
        }
        
        $folderpath = $folder == null ? '' : $folder . "/";
        
        if( $transactional == true ){
            $file = helpers::path_get("log") . $folderpath . date('M d, Y') . ".txt";
        } else {
            $newfilename = $filename == null ? 'filename' : $filename;
            $file = helpers::path_get("log") . $folderpath . $newfilename . ".txt";
        }
        
        $logs = date('[d/M/Y h:i:s a] | ') . " " . $logmess . PHP_EOL;
        file_put_contents($file, $logs, FILE_APPEND | LOCK_EX);
    }

}
