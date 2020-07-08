<?php

    if(!defined("load")){
        header("Location:/403");
        exit;
    }
?>
<?= html::header(); ?>
        <div class="jumbotron">
            <h2><?php echo __siteName; ?></h2>
        </div>
<?= html::footer(); ?>