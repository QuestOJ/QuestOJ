<?php

    if(!defined("load")){
        header("Location:/403");
        exit;
    }
?>
<?= html::header(); ?>
        <div class="text-center">
            <div style="font-size:233px">403</div>
            <p>您没有权限访问此网页</p>
        </div>
<?= html::footer(); ?>