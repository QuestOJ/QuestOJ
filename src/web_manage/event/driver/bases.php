<?php

    if (!defined("load")) {
        header("HTTP/1.1 404 Not Found");
        exit;
    }

    function randomStr($length, $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789") {
        $shuffle = str_shuffle($chars);
        $result = '';
        
        for ($i=0; $i < $length; $i++) {
            $index = mt_rand(0,strlen($chars));
            $result .= substr($shuffle,$index,1);
        }

        return $result;
    }

    function getRequestID() {
        define("RequestID", randomStr(32));
    }

    function output($status, $body, $msg) {
        $RequestID = constant("RequestID");

        $response = array(
            "request" => $RequestID,
            "status" => $status,
            "data" => $body,
            "message" => $msg
        );

        echo json_encode($response);
        exit(0);
    }

    function handleError(Throwable $t) {
        $RequestID = constant("RequestID");

        if (substr($t->__toString(), 0, 9) == "Exception") {
            output(false, "", $t->getmessage());
        } else {
            $logDate = date("Ymd");
            $logFile = fopen("data/log/{$logDate}.log","a+");
            $date = date("Y-m-d H:i:s");

            $text = "({$date}) Request {$RequestID}: \n{$t}\n";
            fwrite($logFile, $text);

            fclose($logFile);

            output(false, "", "Internal server error, please contact administrator for help");
        }
    }

    
?>