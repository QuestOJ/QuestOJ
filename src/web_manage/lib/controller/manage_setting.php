<?php

    if (!defined("load") || !isUserLogin()) {
        header("Location:/403");
        exit;
    }

    if (!auth::checkToken()) {
        exit("expired");
    }

    $siteName = db::escape($_POST["sitename"]);
    $siteURL = db::escape($_POST["siteurl"]);
    $siteShortName = db::escape($_POST["siteshortname"]);
    $loginTime = db::escape($_POST["logintime"]);

    if (empty($siteName)) {
        exit();
    }

    if (empty($siteURL)) {
        exit();
    }

    if (empty($siteShortName)) {
        exit();
    }

    if (empty($loginTime)) {
        exit();
    }

    if (!is_numeric($loginTime) || $loginTime < 600) {
        exit();
    }

    if (substr($siteURL, -1) != "/") {
        exit();
    }

    log::writelog(2, 3, 312, "修改系统设置");
    
    updateSystemSetting("__siteName", $siteName);
    updateSystemSetting("__siteURL", $siteURL);
    updateSystemSetting("__siteShortName", $siteShortName);
    updateSystemSetting("__loginTime", $loginTime);
    
    exit("ok");
?>