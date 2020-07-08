<?php

    if (!defined("load") || !isUserLogin()) {
        header("Location:/403");
        exit;
    }

    if (!auth::checkToken()) {
        exit("expired");
    }

    $id = $_POST["id"];
    $level = $_POST["level"];
    $action = db::escape($_POST["action"]);

    if (!is_numeric($id) || (isset($_POST["level"]) && !is_numeric($level))) {
        exit();
    }

    if (!$post = getPostInfo($id)) {
        exit("id");
    }

    if ($action == "add") {
        log::writelog(2, 3, 307, "新增公告 {$post["title"]}");
        db::query("oj", "insert into important_blogs (`blog_id`,`level`) VAlUES ('$id', '$level')");
        db::commit("oj");
        exit("ok");
    } else if ($action == "edit") {
        log::writelog(2, 3, 308, "修改公告 {$post["title"]} 置顶等级");
        db::query("oj", "update important_blogs SET level = '$level' where blog_id = '$id'");
        exit("ok");
    } else if ($action == "delete") {
        log::writelog(2, 3, 309, "删除公告 {$post["title"]}");
        db::query("oj", "delete from important_blogs where blog_id = '$id'");
        exit("ok");        
    }
?>