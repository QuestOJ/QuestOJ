<?php

    if(!defined("load")){
        header("Location:/403");
        exit;
    }
?>
<?= html::header(); ?>
        <div class="text-center">
            <div style="font-size:233px">404</div>
            <p>您请求的页面不存在</p>
        </div>
<?= html::footer(); ?>