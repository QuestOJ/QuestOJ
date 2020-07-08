<?php

    if (!defined("load")){
        header("Location:/403");
        exit;
    }

    if (isUserLogin()){
        header("Location:/");
        exit;
    }

    if ($_GET["callback"] == true) {
        if (auth::verify()) {
            header("Location:/");
            exit;
        }

        header("Location:/403");
        exit;
    }

    auth::login();
    exit;
?>