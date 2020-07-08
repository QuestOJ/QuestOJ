<?php

    if (!defined("load")) {
        header("HTTP/1.1 404 Not Found");
        exit;
    }
    class DB{
        private static function localInit() {
            global $localMySQL;

            $CONNECTION_IP = MYSQL_IP;
            
            if(!empty(MYSQL_PORT))
                $CONNECTION_IP = $CONNECTION_IP.":".MYSQL_PORT;

            $localMySQL = mysqli_connect($CONNECTION_IP, MYSQL_USERNAME, MYSQL_PASSWORD);

            if(!$localMySQL){
                log::writeLog(5, 1, 101, "Unable to connect to the database");
            }

            if(!mysqli_select_db($localMySQL, MYSQL_DATABASE)){
                log::writeLog(5, 1, 101, mysqli_error($localMySQL));
            }

            mysqli_set_charset($localMySQL, 'utf8mb4');
            return $localMySQL;
        }

        public static function init() {
            self::localInit();
        }

        public static function query($q) {
            global $localMySQL;
            return mysqli_query($localMySQL, $q);
        }

        public static function commit() {
            global $localMySQL;
            return mysqli_commit($localMySQL);
        }

        public static function num_rows($q) {
            return mysqli_num_rows(self::query($q));
        }

        public static function escape($q) {
            global $localMySQL;
            return mysqli_escape_string($localMySQL, $q);
        }

        public static function insert_id() {
            global $localMySQL;
            return mysqli_insert_id($localMySQL);           
        }
    }
?>