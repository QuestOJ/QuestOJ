<?php

	$frameworkPATH = dirname(__FILE__);

	/**
	 * framework_load : 载入信号
	 * framework_version : 版本信息
	 */

	define("framework_load", true);
	define("framework_version", "3.1");
	define("framework_ipdat_version", "20200115");

	/**
	 * 注册 Framework 子框架
	 * 
	 * @param string $name
	 * @return NULL
	 */

	function FrameworkRegister($name){
		global $frameworkPATH;

		if(!include_once($frameworkPATH."/frame_".$name.".php")){
			exit("Oops: Something goes wrong with framework loading...");
		}

		return new $name();
	}

	$CLASS_LOCATION = FrameworkRegister("location");
	$CLASS_REPORT = FrameworkRegister("report");

	$framework_version = framework_version;
	$framework_ipdat_version = framework_ipdat_version;

	/**
	 * 返回框架版本
	 * 
	 * @param NULL
	 * @return string $version
	 */

	class frame {
		public static function version(){
			return array(
				"framework_version" => framework_version,
				"framework_ipdat_version" => framework_ipdat_version
			);
		}

		/**
		 * 断言
		 * 
		 * 当传入表达式为真时，断言成立
		 * 当传入表达式为假时，抛出一个致命异常
		 * 
		 * @param string $statement
		 * @return NULL
		 */

		public static function checkStatement($result, $text = "No details"){
			if($result == FALSE || $result == 0){
				self::dump("-1", "Assert failed - {$text}");
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

		public static function oneWayEncryption($text, $key, $length = 32){
			self::checkStatement($length % 2 == 0, "length must be multiple of 2");
			self::checkStatement($length <= 64, "length must be no more than 64");
			self::checkStatement($length > 0, "length must be more than 0");

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

		public static function twoWayEncryption($text, $key, $salt = ""){		
			$exchangeKey = self::oneWayEncryption($salt, $key);

			$encodeData = openssl_encrypt($text, "des-ede3", $exchangeKey, 0);
			$checkData = self::oneWayEncryption($text, $salt, 8);

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

		public static function twoWayDecryption($text, $key, $salt = ""){
			$exchangeKey = self::oneWayEncryption($salt, $key);

			$encodeData = substr($text, -(strlen($text) - 8));
			$encodeCheck = substr($text, 0, 8);

			$data = openssl_decrypt($encodeData, "des-ede3", $exchangeKey, 0);

			if(self::oneWayEncryption($data, $salt, 8) != $encodeCheck)
				return NULL;
			
			return $data;
		}
		
		/**
		 * 生成一个随机字符串
		 * 
		 * @param int $length
		 * 字符串长度
		 * @param string $char
		 * 字符集
		 * @return string
		 */

		public static function randString($length = 32, $char = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ') {
			if(!is_int($length) || $length < 0) {
				return false;
			}
		
			$string = '';
			for($i = $length; $i > 0; $i--) {
				$string .= $char[mt_rand(0, strlen($char) - 1)];
			}
		
			return $string;
		}

		/**
		 * 获取访客 IP
		 * 
		 * @param NULL
		 * @return string $ip
		 */

		function getIP(){
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

		/**
		 * 获取 IP 所在地
		 * 
		 * @param string $ip
		 * IP地址
		 * @return string $location
		 */
		
		public static function getIPLocation($ip){
			global $CLASS_LOCATION;
			return $CLASS_LOCATION->getlocation($ip)['country'];  
		}

		/**
		 * 获取请求地址
		 * 
		 * @param null
		 * @return string
		 */
		
		public static function getURL(){
			if($_SERVER["SERVER_PORT"] == 443)
				return 'https://'.$_SERVER['SERVER_NAME'].':'.$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"]; 
			else
				return 'http://'.$_SERVER['SERVER_NAME'].':'.$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"]; 
		}

		/**
		 * 获取访客浏览器信息
		 * 
		 * @param null
		 * @return string
		 */

		public static function getBrowser(){
			return $_SERVER['HTTP_USER_AGENT'];
		} 

		/**
		 * 获取访客操作系统
		 * 
		 * @param null
		 * @return string
		 */

		public static function getOS(){
			if(!empty($_SERVER['HTTP_USER_AGENT'])){
				$os = $_SERVER['HTTP_USER_AGENT'];

				if(preg_match('/win/i', $os)){
					$os = 'Windows';
				}else if(preg_match('/mac/i', $os)){
					$os = 'MAC';
				}else if(preg_match('/linux/i', $os)){
					$os = 'Linux';
				}else if(preg_match('/unix/i', $os)){
					$os = 'Unix';
				}else if(preg_match('/bsd/i', $os)){
					$os = 'BSD';
				}else{
					$os = 'Other';
				}

				return $os;

			}else{
				return 'Unknown';
			}
		}

		/**
		 * 创建一个 Session
		 * 
		 * @param string $name
		 * @param string $text
		 */

		public static function createSession($name, $text){
			$_SESSION[$name] = self::twoWayEncryption($text, AUTH_KEY.self::clientKey(), SESSION_SALT);
		}

		/**
		 * 检查 Session 是否存在
		 * 
		 * @param string $name
		 * @return bool
		 */
		
		public static function issetSession($name){
			if(!empty($_SESSION[$name])){
				if(self::twoWayDecryption($_SESSION[$name], AUTH_KEY.self::clientKey(), SESSION_SALT) != NULL)
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
		
		public static function readSession($name){
			if(!empty($_SESSION[$name])){
				return self::twoWayDecryption($_SESSION[$name], AUTH_KEY.self::clientKey(), SESSION_SALT);
			}

			return NULL;
		}
		
		/**
		 * 删除 Session
		 * 
		 * @param string $sessionName
		 * @return null
		 */

		public static function deleteSession($name){
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

		public static function createCookie($name, $text, $time = 7 * 24 * 3600){
			if($time == 0){
				setcookie($name, self::twoWayEncryption($text, AUTH_KEY.self::clientKey(), COOKIE_SALT), NULL , '/');
			}else{
				setcookie($name, self::twoWayEncryption($text, AUTH_KEY.self::clientKey(), COOKIE_SALT), time()+$time, '/');
			}
		}

		/**
		 * 检查 Cookie 是否存在
		 * 
		 * @param string $name
		 * @return bool
		 */

		public static function issetCookie($name){
			if(!empty($_COOKIE[$name])){
				if(self::twoWayDecryption($_COOKIE[$name], AUTH_KEY.self::clientKey(), COOKIE_SALT) != FALSE)
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

		public static function readCookie($name){
			if(!empty($_COOKIE[$name])){
				return self::twoWayDecryption($_COOKIE[$name], AUTH_KEY.self::clientKey(), COOKIE_SALT);
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

		public static function deleteCookie($name, $time = 7 * 24 * 3600){
			setcookie($name, NULL, time()-$time, '/');
		}
		
		/**
		 * 创建一个客户端身份识别 Key
		 * 
		 * @param null
		 * @return string $key
		 */

		public static function clientKey(){
			if(!isset($_COOKIE['_key'])){
				$key = self::randString(32, "abcdefghijklmnopqrstuvwxyz0123456789");
				setcookie("_key", self::twoWayEncryption($key, AUTH_KEY, COOKIE_SALT), time() + 24 * 30 * 3600, '/');
			}else{
				$key = self::twoWayDecryption($_COOKIE['_key'], AUTH_KEY, COOKIE_SALT);
			}

			return $key;
			}

		/**
		 * 输出异常信息
		 * 
		 * @param string $code
		 * 错误代码
		 * @param string $name
		 * 错误信息
		 * @param string $siteName
		 * 站点名称
		 * @param string $siteUrl
		 * 站点地址
		 * 
		 * @return null
		 */

		public static function dump($code, $name, $siteName = "", $siteUrl = "", $stack = NULL){
			global $CLASS_REPORT;

			// Is Disabled Debug Info
			$debug = 1;
			if(constant("__disabledDebug") == 1)
				$debug = 0;

			$CLASS_REPORT->HTMLPrint($code, $name, $siteName, $siteUrl, $stack, $debug);
			}

		/**
		 * 向浏览器返回一个 HTTP 错误信息
		 * 
		 * @param int $code
		 * @return NULL
		 * 
		 */

		public static function HTTPCode($code){
			static $http = array (
				100 => "HTTP/1.1 100 Continue",
				101 => "HTTP/1.1 101 Switching Protocols",
				200 => "HTTP/1.1 200 OK",
				201 => "HTTP/1.1 201 Created",
				202 => "HTTP/1.1 202 Accepted",
				203 => "HTTP/1.1 203 Non-Authoritative Information",
				204 => "HTTP/1.1 204 No Content",
				205 => "HTTP/1.1 205 Reset Content",
				206 => "HTTP/1.1 206 Partial Content",
				300 => "HTTP/1.1 300 Multiple Choices",
				301 => "HTTP/1.1 301 Moved Permanently",
				302 => "HTTP/1.1 302 Found",
				303 => "HTTP/1.1 303 See Other",
				304 => "HTTP/1.1 304 Not Modified",
				305 => "HTTP/1.1 305 Use Proxy",
				307 => "HTTP/1.1 307 Temporary Redirect",
				400 => "HTTP/1.1 400 Bad Request",
				401 => "HTTP/1.1 401 Unauthorized",
				402 => "HTTP/1.1 402 Payment Required",
				403 => "HTTP/1.1 403 Forbidden",
				404 => "HTTP/1.1 404 Not Found",
				405 => "HTTP/1.1 405 Method Not Allowed",
				406 => "HTTP/1.1 406 Not Acceptable",
				407 => "HTTP/1.1 407 Proxy Authentication Required",
				408 => "HTTP/1.1 408 Request Time-out",
				409 => "HTTP/1.1 409 Conflict",
				410 => "HTTP/1.1 410 Gone",
				411 => "HTTP/1.1 411 Length Required",
				412 => "HTTP/1.1 412 Precondition Failed",
				413 => "HTTP/1.1 413 Request Entity Too Large",
				414 => "HTTP/1.1 414 Request-URI Too Large",
				415 => "HTTP/1.1 415 Unsupported Media Type",
				416 => "HTTP/1.1 416 Requested range not satisfiable",
				417 => "HTTP/1.1 417 Expectation Failed",
				500 => "HTTP/1.1 500 Internal Server Error",
				501 => "HTTP/1.1 501 Not Implemented",
				502 => "HTTP/1.1 502 Bad Gateway",
				503 => "HTTP/1.1 503 Service Unavailable",
				504 => "HTTP/1.1 504 Gateway Time-out"
			);
			
			header($http[$code]);
		}
	}

	if(!defined("load")){
		echo "Framework ".frame::version()["framework_version"]." with IP database ".frame::version()["framework_ipdat_version"]."<br/>";
	}


?>