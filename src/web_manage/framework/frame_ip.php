<?php
    /**
     * 子框架 : 获取IP信息 (/framework/frame_ip.php)
     */

    if(!defined("framework_load")){
        header("Location:/403");
    }

    class IP{
        /**
         * Get IP Address
         * 
         * @param null
         * @return string
         */   

        function getIP() {
            static $realip = NULL;
            
            if ($realip !== NULL) {
                return $realip;
            }
            
            if (getenv( 'HTTP_X_FORWARDED_FOR')) {
                $realip = getenv( 'HTTP_X_FORWARDED_FOR');
            } elseif (getenv( 'HTTP_CLIENT_IP')) {
                $realip = getenv( 'HTTP_CLIENT_IP');
            } else {
                $realip = getenv( 'REMOTE_ADDR');
            }
            
            preg_match("/[\d\.]{7,15}/", $realip, $onlineip);
            $realip = !empty($onlineip[0]) ? $onlineip[0] : '0.0.0.0';
            
            return $realip;
        }
    }
?>