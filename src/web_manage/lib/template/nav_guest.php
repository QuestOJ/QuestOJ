<?php

    if(!defined("load")){
        header("Location:/403");
        exit;
    }    
?>

<body>
    <div class="container">
        <nav class="navbar navbar-expand-lg navbar-light bg-light mb-4">
            <a class="navbar-brand" href="<?= __siteUrl; ?>"><?= __siteShortName; ?></a>
            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarSupportedContent">
                <ul class="navbar-nav mr-auto">
                </ul>
                <ul class="nav navbar-nav navbar-right">
                    <li class="nav-item">
                        <a class = "nav-link" href="/login">登录</a>
                    </li>    
                </ul>
            </div>
        </nav>
    </div>

    <div class="container">