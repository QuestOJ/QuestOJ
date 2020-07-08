<?php

    if (empty($_GET["token"])) {
        die("Authentication token required (101)");
    }

    if (empty($_GET["request"])) {
        die("Request ID required (201)");
    }

    $token = DB::escape($_GET["token"]);
    $request = DB::escape($_GET["request"]);

    if (!API::checkClient($token)) {
        die("Authentication failed (110)");
    }

    if (!API::checkRequest($token, $request, "login")) {
        die("Authentication failed (210)");
    }

    if (Auth::check()) {
        if (!isSuperUser(Auth::user())) {
            become403Page();
        }
        API::finishRequest($token, $request, "success", Auth::id());
        API::callback($token, $request);
        die();
    }

    $_SESSION["callback"] = "/api/auth?token=$token&request=$request";
    header("Location:/login");
?>