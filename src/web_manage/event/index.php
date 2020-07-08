<?php
    
    error_reporting(0);

    ini_set('zend.assertions', 1);
    ini_set('assert.exception', 1);

    date_default_timezone_set('PRC');

    define("load", true);

    if (!include_once("load.php")) {
        header("HTTP/1.1 500 Internal Server Error");
        exit;
    }
?>