<?php

    /**
     * 断言
     * 
     * 当传入表达式为真时，断言成立
     * 当传入表达式为假时，抛出一个错误
     * 
     * @param string $statement
     * @return NULL
     */

    function checkStatement($result, $text = "No details"){
        if($result == FALSE || $result == 0){
            throw new Error("Assert failed - {$text}");
        }
    }

    /**
     * 不可逆加密
     *
     * @param string $text
     * 被加密文本
     * @param string $key
     * 加密秘钥
     * @param int $length
     * 密文长度
     * @return string
     */

    function oneWayEncryption($text, $key, $length = 32){
        checkStatement($length % 2 == 0, "length must be multiple of 2");
        checkStatement($length <= 64, "length must be no more than 64");
        checkStatement($length > 0, "length must be more than 0");

        return substr(md5(md5($key.$text).$key), 0, $length/2).substr(md5($key.md5($text.$key)), 0, $length/2);
    }

    /**
     * 可逆加密

        * @param string $text
        * 被加密文本
        * @param string $key
        * 加密秘钥
        * @param string $salt
        * 加密盐
        * @return string
        */

    function twoWayEncryption($text, $key, $salt = ""){		
        $exchangeKey = oneWayEncryption($salt, $key);

        $encodeData = openssl_encrypt($text, "des-ede3", $exchangeKey, 0);
        $checkData = oneWayEncryption($text, $salt, 8);

        return $checkData.$encodeData;
    }

    /**
     * 解密
     * 
     * 解密成功返回明文，解密失败返回 FALSE
     * 
     * @param string $text
     * 被加密文本
     * @param string $key
     * 加密秘钥 
     * @param string $salt
     * 加密盐
     * @return string
     */

    function twoWayDecryption($text, $key, $salt = ""){
        $exchangeKey = oneWayEncryption($salt, $key);

        $encodeData = substr($text, -(strlen($text) - 8));
        $encodeCheck = substr($text, 0, 8);

        $data = openssl_decrypt($encodeData, "des-ede3", $exchangeKey, 0);

        if (oneWayEncryption($data, $salt, 8) != $encodeCheck)
            return NULL;
        
        return $data;
    }

    /**
     * 获取访客 IP
     * 
     * @param NULL
     * @return string $ip
     */

    function getIP() {
        static $realip = NULL;
     
        if ($realip !== NULL) {
            return $realip;
        }
     
        if (getenv('HTTP_X_FORWARDED_FOR')) {
            $realip = getenv('HTTP_X_FORWARDED_FOR');
        } elseif (getenv('HTTP_CLIENT_IP')) {
            $realip = getenv('HTTP_CLIENT_IP');
        } else {
            $realip = getenv('REMOTE_ADDR');
        }
     
        preg_match("/[\d\.]{7,15}/", $realip, $onlineip);
        $realip = !empty($onlineip[0]) ? $onlineip[0] : '0.0.0.0';
     
        return $realip;
    }

    /**
     * 创建一个 Session
     * 
     * @param string $name
     * @param string $text
     */

    function createSession($name, $text){
        $_SESSION[$name] = twoWayEncryption($text, AUTH_KEY.$this->clientKey(), SESSION_SALT);
    }

    /**
     * 检查 Session 是否存在
     * 
     * @param string $name
     * @return bool
     */
    
    function issetSession($name){
        if(!empty($_SESSION[$name])){
            if(twoWayDecryption($_SESSION[$name], clientKey(), SESSION_SALT) != NULL)
                return TRUE;
        }

        return FALSE;
    }

    /**
     * 读取 Session 内容
     * 如果 Session 不存在，返回 NULL
     * 
     * @param string $name
     * @return string
     */
    
    function readSession($name){
        if(!empty($_SESSION[$name])){
            return twoWayDecryption($_SESSION[$name], clientKey(), SESSION_SALT);
        }

        return NULL;
    }
    
    /**
     * 删除 Session
     * 
     * @param string $sessionName
     * @return null
     */

    function deleteSession($name){
        unset($_SESSION[$name]);
    }

    /**
     * 创建一个 Cookie
     * 
     * 当 $cookieTime 为 0 时，为会话 Cookie
     * 
     * @param string $cookieName
     * @param string $cookieText
     * @param string $cookieTime
     * 
     * @return null
     */

    function createCookie($name, $text, $time = 7 * 24 * 3600){
        if($time == 0){
            setcookie($name, twoWayEncryption($text, AUTH_KEY.clientKey(), COOKIE_SALT), NULL , '/');
        }else{
            setcookie($name, twoWayEncryption($text, AUTH_KEY.clientKey(), COOKIE_SALT), time()+$time, '/');
        }
    }

    /**
     * 检查 Cookie 是否存在
     * 
     * @param string $name
     * @return bool
     */

    function issetCookie($name){
        if(!empty($_COOKIE[$name])){
            if(twoWayDecryption($_COOKIE[$name], AUTH_KEY.clientKey(), COOKIE_SALT) != FALSE)
                return TRUE;
        }

        return FALSE;
    }

    /**
     * 读取 Cookie 内容
     * 如果 Cookie 不存在，返回 FALSE
     * 
     * @param string $name
     * @return string
     */

    function readCookie($name){
        if(!empty($_COOKIE[$name])){
            return twoWayDecryption($_COOKIE[$name], AUTH_KEY.clientKey(), COOKIE_SALT);
        }

        return FALSE;			
    }

    /**
     * 删除 Cookie
     * 
     * @param string $cookieName
     * @param string $cookieTime
     * @return null
     */

    function deleteCookie($name, $time = 7 * 24 * 3600){
        setcookie($name, NULL, time()-$time, '/');
    }

    /**
     * 创建一个客户端身份识别 Key
     * 
     * @param null
     * @return string $key
     */

    function clientKey(){
        if(!isset($_COOKIE['_key'])){
            $key = $this->randString(32, "abcdefghijklmnopqrstuvwxyz0123456789");
            setcookie("_key", $key, time() + 24 * 30 * 3600, '/');
        }else{
            $key = $_COOKIE['_key'];
        }

        return $key;
    }
?>