<?php

    if (empty($_POST["token"])) {
        die("Authentication token required (101)");
    }

    if (empty($_POST["secret"])) {
        die("Authentication secret required (102)");
    }

    if (empty($_POST["action"])) {
        die("Request action required (202)");
    }

    if (empty($_POST["callback"])) {
        die("Request callback URL required (203)");
    }

    $token = DB::escape($_POST["token"]);
    $secret = DB::escape($_POST["secret"]);
    $action = DB::escape($_POST["action"]);
    $callback = DB::escape($_POST["callback"]);

    if (!API::checkClient($token, $secret)) {
        die("Authentication failed (110)");
    }

    $request = API::registerRequest($token, $action, $callback);
    die($request);

?>