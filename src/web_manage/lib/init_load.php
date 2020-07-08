<?php

    /**
     * 载入系统文件 (init_load.php)
     */
    
    if(!defined("load")){
        header("Location:/403");
        exit;
    }

    /**
     * 载入 Framework 接口
     */
    
    if(!include_once(PATH."/framework/frame.php")){
        exit("Oops: Loading Failed - Something goes wrong with framework API - No such file");
    }
    
    if(MININUM_FRAMRWORK_VERSION > framework_version){
        frame::dump("-1", "Mininum Framework Version ".MININUM_FRAMRWORK_VERSION);
    }

    define("RequestID", time().frame::randString(6,"0123456789"));

    /**
     * 异常处理
     */

    function handleError(Throwable $t) {
        $RequestID = constant("RequestID");

        $logDate = date("Ymd");
        $logFile = fopen("data/log/{$logDate}.err.log","a+");
        $date = date("Y-m-d H:i:s");

        $text = "({$date}) Request {$RequestID}: \n{$t}\n";
        fwrite($logFile, $text);
        fclose($logFile);

        frame::dump(-1, $t->getMessage(), NULL, NULL, $t->getTrace());
    }
    
    try {
        function loadFile($filepath) {
            if (!include_once($filepath)) {
                throw new Error("Failed to require ". $filepath);
            }
        }

        spl_autoload_register(function($class_name) {
            loadFile(PATH."/lib/lib_". $class_name . '.php');
        });
        
        loadFile(PATH."/data/config/config.inc.php");

        db::init();
        
        loadFile(PATH."/lib/lib_function.php");
        loadSystemSetting();
        
        loadFile(PATH."/lib/init_route.php");
        
    } catch (Throwable $t) {
        handleError($t);
    }
?>