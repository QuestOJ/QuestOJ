<?php

    if (!defined("load")) {
        header("HTTP/1.1 404 Not Found");
        exit;
    }

    if (!include_once("driver/bases.php")) {
        header("HTTP/1.1 500 Internal Server Error");
        exit;
    }

    getRequestID();

    try {

        function loadFile($filepath) {
            if (!include_once($filepath)) {
                throw new Error("Failed to require ". $filepath);
            }
        }

        loadFile("data/config.inc.php");
        loadFile("driver/interface.php");
        
        loadFile("driver/database.php");
        db::init();

        loadFile("driver/client.php");
        loadFile("driver/handler.php");
        
    } catch (Throwable $t) {
        handleError($t);
    }

?>