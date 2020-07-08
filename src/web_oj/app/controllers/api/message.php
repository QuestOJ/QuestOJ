<?php

    if (empty($_POST["token"])) {
        die("Authentication token required (101)");
    }

    if (empty($_POST["secret"])) {
        die("Authentication secret required (102)");
    }

    if (empty($_POST["username"])) {
        die("Message title required (201)");
    }

    if (empty($_POST["title"])) {
        die("Message title required (202)");
    }

    if (empty($_POST["content"])) {
        die("Message Content required (203)");
    }

    $token = DB::escape($_POST["token"]);
    $secret = DB::escape($_POST["secret"]);
    $username = DB::escape($_POST["username"]);
    $title = DB::escape($_POST["title"]);
    $content = DB::escape($_POST["content"]);

    if (!API::checkClient($token, $secret)) {
        die("Authentication failed (110)");
    }

    if (validateUsername($username) && ($user = queryUser($username))) {
        sendSystemMsg($username, $title, $content);
        die("success");
    } else {
        die("No such user (210)");
    }
?>