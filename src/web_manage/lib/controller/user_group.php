<?php

    if (!defined("load") || !isUserLogin()) {
        header("Location:/403");
        exit;
    }

    if (!auth::checkToken()) {
        exit("expired");
    }

    $id = $_POST["id"];
    $groupname = db::escape($_POST["groupname"]);
    $comments = db::escape($_POST["comments"]);

    if (empty($groupname)) {
        exit("");
    }

    if ($_POST["action"] == "delete") {
        if (!is_numeric($id) || $id == 0) {
            exit("");
        }
        
        log::writelog(2, 3, 305, "删除用户组 {$groupname}");

        db::query("oj", "update user_info SET userdefine = '0' where userdefine = '$id'");
        db::query("oj", "delete from usergroup where id = '$id'");

    } else if ($_POST["action"] == "edit") {
        if (!is_numeric($id)) {
            exit("");
        }
        
        if (db::num_rows("oj", "select name from usergroup where name = '{$groupname}' and id != '{$id}'")) {
            exit("groupname");
        }

        log::writelog(2, 3, 304, "修改用户组 {$groupname} 信息");

        db::query("oj", "update usergroup SET name = '{$groupname}', comments = '{$comments}' where id = '$id'");
    } else if($_POST["action"] == "add") {
        if (db::num_rows("oj", "select name from usergroup where name = '{$groupname}'")) {
            exit("groupname");
        }

        log::writelog(2, 3, 303, "新增用户组 {$groupname}");

        db::query("oj", "insert into usergroup (`name`, `comments`) VALUES ('$groupname', '$comments')");
    } else {
        exit("");
    }

    exit("ok");
?>