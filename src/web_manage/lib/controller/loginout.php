<?php

    /**
     * 控制器 : 用户登出
     */
    
    if(!defined("load") || !isUserLogin()){
        header("Location:/403");
        exit;
    }
    
    frame::deleteSession("username");
    frame::deleteCookie("loginstamp");

    header("Location:/");
?>