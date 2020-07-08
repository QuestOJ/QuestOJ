<?php

    /**
     * 执行库：数据库请求 (/lib/lib_database.php)
     */

    if(!defined("load")){
        header("Location:/403");
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

        private static function ojInit() {
            global $ojMySQL;

            $CONNECTION_IP = OJ_MYSQL_IP;
            
            if(!empty(OJ_MYSQL_PORT))
                $CONNECTION_IP = $CONNECTION_IP.":".OJ_MYSQL_PORT;

            $ojMySQL = mysqli_connect($CONNECTION_IP, OJ_MYSQL_USERNAME, OJ_MYSQL_PASSWORD);

            if(!$ojMySQL){
                log::writeLog(5, 1, 101, "Unable to connect to the database");
            }

            if(!mysqli_select_db($ojMySQL, OJ_MYSQL_DATABASE)){
                log::writeLog(5, 1, 101, mysqli_error($ojMySQL));
            }

            mysqli_set_charset($ojMySQL, 'utf8mb4');
            return $ojMySQL;
        }

        public static function init() {
            self::localInit();
            self::ojInit();
        }

        public static function query($con, $q) {
            global $localMySQL;
            global $ojMySQL;

            if ($con == "local")
                return mysqli_query($localMySQL, $q);
            else
                return mysqli_query($ojMySQL, $q);
        }

        public static function commit($con) {
            global $localMySQL;
            global $ojMySQL;

            if ($con == "local")
                return mysqli_commit($localMySQL);
            else
                return mysqli_commit($ojMySQL);
        }

        public static function num_rows($con, $q) {
            return mysqli_num_rows(self::query($con, $q));
        }

        public static function escape($q) {
            global $localMySQL;
            return mysqli_escape_string($localMySQL, $q);
        }

        public static function selectFirst($con, $q, $opt = MYSQLI_ASSOC) {
            global $uojMySQL;
            return mysqli_fetch_array(self::query($con, $q), $opt);
        }
    }
?>