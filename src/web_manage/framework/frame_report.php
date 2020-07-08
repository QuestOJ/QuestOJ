<?php
    
    /**
	 * 子框架 : 处理异常信息 (/framework/frame_report.php)
	 */

    if(!defined("framework_load")){
        header("Location:/403");
    }

    class report{
        public static function HTMLPrint($logCode, $logText, $siteName, $siteUrl, $stack, $debug){
            if ($stack == NULL) {
                $stackArray = debug_backtrace();
            } else {
                $stackArray = $stack;
            }
            
            if(!include_once("frame_report_HTMLPrint.php")){
                echo "<h2><font color='red'>System Error - Failed to handling error messages</font></h2><br/>";
            }
            
            exit;
        }
    }
?>