<?php

/*
 * Author: Ivan Paul Bay
 * Date: May 23, 2016
 * File: bootstrap/autoload.php
 */

use larapers\libs\helpers;

/**
 * Autoload classes
 *
 * @author Ivan Paul Bay <ivan.bay@maximintegrated.com>
 * @param string $classname Class to load
 * @return void
 */
function __autoload($classname) {

    $path = include __DIR__ . "/paths.php";
    $base_path = $path['base'];

    $filename = $base_path . "\\" . $classname . ".class.php";

    if (file_exists($filename)) {
        include $filename;
    }
}
