<?php

    /**
     * 禁用非致命错误报错
     */

    error_reporting(E_ERROR | E_PARSE);

    /**
     * 修改时区为 Asia/Shanghai
     */

    date_default_timezone_set('PRC');

    /**
     * 启用 Session
     */

    session_set_cookie_params(30 * 24 * 3600); 
    session_start();
    
    /**
     * 创建载入信号
     * 
     * load : true
     * PATH : 工作目录
     */

    define("load", true);
    define("PATH", dirname(__FILE__));
    define("MININUM_FRAMRWORK_VERSION", "3.1");
    define("VERSION", "1.0");
    
    if(!include_once("lib/init_load.php")){
        exit("Oops: Failed to execute init target - No such file");
    }
?>