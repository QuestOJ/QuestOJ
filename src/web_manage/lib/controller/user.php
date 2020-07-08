<?php

    if (!defined("load") || !isUserLogin()) {
        header("Location:/403");
        exit;
    }

    if (!auth::checkToken()) {
        exit("expired");
    }

    $action = db::escape($_POST["action"]);
    $username = $_POST["username"];

    if (!validateUsername($username)) {
        exit();
    }

    if (!$user = getUserInfo($username)) {
        exit("username");
    }

    if ($action == "ban") {
        log::writelog(2, 3, 301, "禁用账户 {$username}");

        if ($user["usergroup"] == "B") {
            log::writelog(2, 2, 3011, "账户已禁用,无需操作");
        }

        db::query("oj", "UPDATE `user_info` SET `usergroup` = 'B' where username = '{$username}'");
    } else if ($action == "active") {
        log::writelog(2, 3, 301, "启用账户 {$username}");

        if ($user["usergroup"] != "B") {
            log::writelog(2, 2, 3012, "帐户已启用,无需操作");
        }

        db::query("oj", "UPDATE `user_info` SET `usergroup` = 'U' where username = '{$username}'");
    } else if($action == "edit") {
        $realname = $_POST["realname"];
        $email = $_POST["email"];
        $userdefine = $_POST["userdefine"];
        $usergroup = $_POST["usergroup"];

        if (!validateRealname($realname)) {
            exit();
        }

        if (!validateEmail($email)) {
            exit();
        }

        if (!is_numeric($userdefine)) {
            exit();
        }

        if ($usergroup != "Y" && $usergroup != "N") {
            exit();
        }

        if (!getGroupInfo($userdefine)) {
            exit();
        }

        if (db::num_rows("oj", "select username from user_info where email = '{$email}' and username != '{$username}'")) {
            exit("email");
        }

        log::writelog(2, 3, 302, "修改账户 {$username} 信息");

        if ($usergroup == "Y") {
            db::query("oj", "update user_info SET usergroup = 'S' where username = '{$username}'");
        }

        if ($usergroup == "N" && $user["usergroup"] != "B") {
            db::query("oj", "update user_info SET usergroup = 'U' where username = '{$username}'");
        }

        db::query("oj", "update user_info SET userdefine = '{$userdefine}' where username = '{$username}'");
        db::query("oj", "update user_info SET email = '{$email}' where username = '{$username}'");
        db::query("oj", "update user_info SET realname = '{$realname}' where username = '{$username}'");
        db::query("oj", "update contests_registrants SET realname = '{$realname}' where username = '{$username}'");
    } else {
        exit("");
    }

    exit("ok");
?>