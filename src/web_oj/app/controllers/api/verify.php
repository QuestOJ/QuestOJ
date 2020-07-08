<?php

    if (empty($_POST["token"])) {
        die("Authentication token required (101)");
    }

    if (empty($_POST["secret"])) {
        die("Authentication secret required (102)");
    }

    if (empty($_POST["request"])) {
        die("Request ID required (201)");
    }

    if (empty($_POST["action"])) {
        die("Request action required (202)");
    }

    $token = DB::escape($_POST["token"]);
    $secret = DB::escape($_POST["secret"]);
    $request = DB::escape($_POST["request"]);
    $action = DB::escape($_POST["action"]);

    if (!API::checkClient($token, $secret)) {
        die("Authentication failed (110)");
    }

    $status = API::checkRequest($token, $request, $action, "success");
    $data = API::getRequestData($token, $request);

    $array = array (
        "status" => true,
        "data" => $data
    );

    if (!$status) {
        $array["status"] = false;
    }

    die(json_encode($array));
?>