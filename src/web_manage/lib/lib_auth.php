<?php

    class auth{
        private static function request($url, $method, $requestData=array()){
            $curl = curl_init();
    
            curl_setopt($curl, CURLOPT_URL, $url);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
           
            if ($method == "POST") {
                curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($requestData));
            }

            $res = curl_exec($curl);
            curl_close($curl);

            return $res;
        }

        private function register($action, $callback="/") {
            $registerData = array(
                'token' => API_TOKEN,
                'secret' => API_SECRET,
                'action' => $action,
                'callback' => __siteUrl.$callback
            );

            return self::request(OJ_URL."/api/register", "POST", $registerData);
        }

        private function verifySuccess($username) {
            frame::createSession("username", $username);
            frame::createCookie("loginstamp", time(), 0);
        }
        
        public function login() {
            $code = self::register("login", "login?callback=true");

            if (strlen($code) != 32 || !is_numeric(substr($code, 0, 10))) {
                log::writeLog(7, 1, 201, "创建登录请求失败", $code);
            }

            frame::createSession("request", $code);

            header("Location:".OJ_URL."/api/auth?token=".API_TOKEN."&request=".$code);
        }

        public function verify() {
            $registerData = array(
                'token' => API_TOKEN,
                'secret' => API_SECRET,
                'action' => 'login',
                'request' => frame::readSession("request")
            );

            if (empty($registerData["request"])) {
                log::writeLog(2, 2, 202, "登录验证失败", "没有登录请求");
                return false;
            }

            $status = self::request(OJ_URL."/api/verify", "POST", $registerData);
            $data = json_decode($status, true);

            frame::deleteSession("request");

            if ($data["status"] == true) {
                self::verifySuccess($data["data"]);
                return true;
            }

            log::writeLog(2, 1, 202, "登录验证失败", $status);
            return false;
        }

        public function sendMessage($username, $title, $content) {
            $registerData = array(
                'token' => API_TOKEN,
                'secret' => API_SECRET,
                'title' => $title,
                'content' => $content,
                'username' => $username
            );

            $status = self::request(OJ_URL."/api/message", "POST", $registerData);

            if ($status != "success") {
                log::writeLog(2, 1, 203, "发送系统消息失败", $status);
            }
        }

        public static function checkToken() {
            if (($_GET["token"] != frame::clientKey()) && ($_POST["token"] != frame::clientKey())) {
                return false;
            }
            return true;
        }
    }
?>