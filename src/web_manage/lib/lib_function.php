<?php

    /**
     * 执行库 : 常用函数定义 (/lib/lib_function.php)
     */

    if(!defined("load")){
        header("Location:/403");
        exit;
    }
    
    /**
     * 获取系统设置
     * 
     * @param string $name
     * @return string
     */

    function getSystemSetting($name){
        $query = db::query("local", "SELECT * FROM `".MYSQL_TABLE_PREFIX."_system` where `name` = '$name' LIMIT 1");

        while($query_row = mysqli_fetch_assoc($query)){
            return $query_row['value'];
        }
    }

    /**
     * 载入系统设置
     * 
     * 将数据库中 'autoload' 字段为 '1' 的设置设为全局常量
     * 
     * @param null
     * @return null
     */

     function loadSystemSetting(){
        $query = db::query("local", "SELECT * FROM `".MYSQL_TABLE_PREFIX."_system` where `autoload` = '1'");

        while($query_row = mysqli_fetch_assoc($query)){
            define($query_row['name'], $query_row['value']);
        }
     }

    /**
     * 更新系统设置
     * 
     * @param string $name
     * @param string $value
     * @return null
     */

    function updateSystemSetting($name, $value){
        db::query("local", "UPDATE `".MYSQL_TABLE_PREFIX."_system` SET `value`='$value' where `name` = '$name'");
    }
     
    /**
     * 检查用户登录状态
     * 
     * @param null
     * @return bool
     */

    function isUserLogin(){
        if(frame::issetSession("username")){
            $loginTime = frame::readCookie("loginstamp");
            if($loginTime && time() - $loginTime < __loginTime)
                return true;
        }

        return false;
    }

    /**
     * 获取用户信息
     * 
     * @param string $name
     * @return string $value
     */

    function getUserInfo($username) {
        return db::selectFirst("oj", "SELECT * FROM `user_info` where username = '$username'");
    }

    /**
     * 获取用户组信息
     * 
     * @param int $id
     * @return string $value
     */

    function getGroupInfo($id) {
        return db::selectFirst("oj", "SELECT * FROM `usergroup` where id = '$id'");
    }

    /**
     * 获取博客信息
     * 
     * @param int $id
     * @return string $value
     */

    function getPostInfo($id) {
        return db::selectFirst("oj", "SELECT * FROM `blogs` where id = '$id'");
    }

    function getProblemInfo($id) {
        return db::selectFirst("oj", "SELECT * FROM `problems` where id = '$id'");
    }

    function validateUsername($username) {
        return is_string($username) && preg_match('/^[a-zA-Z0-9_]{1,20}$/', $username);
    }
    
    function validateRealname($realname) {
        return is_string($realname) && (preg_match('/^[\x7f-\xff]+$/', $realname) || preg_match('/^[a-zA-Z0-9_]{1,20}$/', $realname));
    }
    
    function validateEmail($email) {
        return is_string($email) && strlen($email) <= 50 && preg_match('/^(.+)@(.+)$/', $email);
    }

    /**
     * 获取任务信息
     * 
     * @param string $name
     * @return string $value
     */

    function getTaskInfo($id) {
        $query = db::query("local", "SELECT * FROM `".MYSQL_TABLE_PREFIX."_task` where `id` = '$id'");

        while($query_row = mysqli_fetch_assoc($query)){
            return $query_row;
        }
     }

    /**
     * 获取任务信息
     * 
     * @param string $name
     * @return string $value
     */

    function getJobInfo($id) {
        $query = db::query("local", "SELECT * FROM `".MYSQL_TABLE_PREFIX."_info` where `id` = '$id'");

        while($query_row = mysqli_fetch_assoc($query)){
            return $query_row;
        }
     }
?>