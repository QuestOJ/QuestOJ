<?php

    /**
     * 执行库 : 系统日志 (/lib/lib_log.php)
     */
    
    if(!defined("load")){
        header("Location:/403");
        exit;
    }

    class log {
        /**
         * 日志写入文件
         * 
         * $logLevel -- 日志等级
         * $logCode -- 日志代码
         * $logText -- 日志信息
         * $logInfo -- 日志详情
         * $execStack -- 执行栈
         * 
         * @param int $logLevel
         * @param int $logCode
         * @param string $logText
         * @param string $logInfo
         * @param string $execStack
         * 
         * @return null
         */

        function writeLogIntoFile($logLevel, $logCode, $logText, $logInfo, $execStack){
            

            $logFilePath = PATH."/data/log/".date("Ymd").".log";
            $logFileStream = fopen($logFilePath, "a+");

            $logUrl = frame::getURL();
            $guestIP = frame::getIP();
            $guestOS = frame::getOS();
            $guestBrowser = frame::getBrowser();
            $userID = frame::readSession("uid");
            $logHash = frame::oneWayEncryption(date("Y-m-d H:i"), $guestIP.$guestOS.$guestBrowser.$userID.$execStack, 64);

            if($logLevel == 1){
                $logLevel = "Error";
            }else if($logLevel == 2){
                $logLevel = "Warning";
            }else{
                $logLevel = "Notice";
            }

            fwrite($logFileStream, date("Y-m-d H:i:s"));
            fwrite($logFileStream, "  |  ");
            fwrite($logFileStream, $logLevel);
            fwrite($logFileStream, "  |  ");
            fwrite($logFileStream, $logCode);
            fwrite($logFileStream, "  |  ");
            fwrite($logFileStream, $logText);
            fwrite($logFileStream, "  |  ");
            fwrite($logFileStream, $logUrl);
            fwrite($logFileStream, "  |  ");
            fwrite($logFileStream, $guestIP);
            fwrite($logFileStream, "  |  ");
            fwrite($logFileStream, $guestBrowser);
            fwrite($logFileStream, "  |  ");
            fwrite($logFileStream, $logHash);
            fwrite($logFileStream, "\r\n");

            fclose($logFileStream);
        }

        /**
         * 日志写入数据库
         * 
         * $logLevel -- 日志等级
         * $logCode -- 日志代码
         * $logText -- 日志信息
         * $logInfo -- 日志详情
         * $execStack -- 执行栈
         * 
         * @param int $logLevel
         * @param int $logCode
         * @param string $logText
         * @param string $logText
         * @param string $execStack
         * 
         * @return null
         */

        function writeLogIntoDatabase($logLevel, $logCode, $logText, $logInfo, $execStack){
            global $localMySQL;

            $logUrl = frame::getURL();

            $guestIP = frame::getIP();
            $guestOS = frame::getOS();
            $guestBrowser = frame::getBrowser();

            $userID = frame::readSession("username");
            $date = date("Y-m-d H:i:s");
            
            $logHash = frame::oneWayEncryption(date("Y-m-d H:i"), $guestIP.$guestOS.$guestBrowser.$userID.$execStack, 64);
            
            $cookieArray = $_COOKIE;

            foreach ($cookieArray as $key => $value) {
                if ($decode = frame::readCookie($key)) {
                    $cookieArray[$key] = $decode;
                }
            }

            $sessionArray = $_SESSION;

            foreach ($sessionArray as $key => $value) {
                if ($decode = frame::readSession($key)) {
                    $sessionArray[$key] = $decode;
                }
            }

            $logText = frame::twoWayEncryption($logText, AUTH_KEY, LOG_SALT);
            $logUrl = frame::twoWayEncryption($logUrl, AUTH_KEY, LOG_SALT);
            $logInfo = frame::twoWayEncryption($logInfo, AUTH_KEY, LOG_SALT);
            $execStack = frame::twoWayEncryption($execStack, AUTH_KEY, LOG_SALT);
            $clientKey = frame::twoWayEncryption(frame::clientKey(), AUTH_KEY, LOG_SALT);
            
            $fieldGet = frame::twoWayEncryption(json_encode($_GET), AUTH_KEY, LOG_SALT);
            $fieldPost = frame::twoWayEncryption(json_encode($_POST), AUTH_KEY, LOG_SALT);
            $fieldCookie = frame::twoWayEncryption(json_encode($cookieArray), AUTH_KEY, LOG_SALT);
            $fieldSession = frame::twoWayEncryption(json_encode($sessionArray), AUTH_KEY, LOG_SALT);

            db::query("local", "INSERT INTO `".MYSQL_TABLE_PREFIX."_logs` (`logLevel`, `logHash`, `logCode`, `logText`, `logInfo`, `logUrl`, `execStack`, `clientKey`, `fieldGet`, `fieldPost`, `fieldCookie`, `fieldSession`, `guestIP`, `guestOS`, `guestBrowser`, `userID`, `date`) VALUES ('$logLevel', '$logHash', '$logCode', '$logText', '$logInfo', '$logUrl', '$execStack', '$clientKey', '$fieldGet', '$fieldPost', '$fieldCookie', '$fieldSession',  '$guestIP', '$guestOS', '$guestBrowser', '$userID', '$date')");

            db::commit("local");
        }

        /**
         * 写入系统日志
         * 
         * $case -- 日志选项
         * $logLevel -- 日志等级
         * $logCode -- 日志代码
         * $logText -- 日志信息
         * $logInfo -- 可选，日志详情
         * 
         * 日志选项为 1 - 7 的数字，由 (0/1) + (0/2) + (0/4) 组成，分别代表 (写入文件) / (写入数据库) / (抛出异常)
         * 日志等级为 1 (Fatal) / 2 (Warning) / 3 (Notice) 构成
         * 
         * @param int $case
         * @param int $logLevel
         * @param int $logCode
         * @param string $logText
         * @param string $logInfo
         * 
         * @return null
         */

        function writeLog($case, $logLevel, $logCode, $logText, $logInfo = ""){        
            $execStack = json_encode(debug_backtrace(), TRUE);

            if($case % 2 == 1){
                self::writeLogIntoFile($logLevel, $logCode, $logText, $logInfo, $execStack);
            }

            if($case % 4 >= 2){
                self::writeLogIntoDatabase($logLevel, $logCode, $logText, $logInfo, $execStack);
            }

            if($case >= 4){
                $SiteName = constant("__siteName");
                $SiteUrl = constant("__siteUrl");

                frame::checkStatement($logLevel == 1, "Receive dump signal but not a fatal error");

                frame::dump($logCode, $logText, $SiteName, $SiteUrl);
            }
        }
    }
?>