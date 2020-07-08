<?php

    if(!defined("load")){
        header("Location:/403");
        exit;
    }    
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    
    <title><?php echo $pageTitle; ?> - <?php echo __siteName; ?></title>

    <!-- Bootstrap -->
    <link href="<?php echo __siteUrl; ?>static/css/bootstrap.min.css" 
    rel="stylesheet">

    <!-- QuestOJ Manage Platform -->
    <link href="<?php echo __siteUrl; ?>static/css/style.css?ver=2020.01.30"
    rel="stylesheet">

    <!-- Fonts -->
    <link rel="stylesheet" id="astrid-body-fonts-css" href="https://cdn.yellowfish.top/fonts.css" type="text/css" media="all">
    <link type="text/css" rel="stylesheet" href="<?= __siteUrl; ?>static/css/bootstrap-glyphicons.min.css?v=2019.5.31">

    <!-- jQuery -->
    <script src="<?php echo __siteUrl; ?>static/js/jquery.min.js"></script>

    <!-- Popper -->
    <script src="<?php echo __siteUrl; ?>static/js/popper.min.js"></script>

    <!-- Bootstrap JS -->
    <script src="<?php echo __siteUrl; ?>static/js/bootstrap.min.js"></script>

    <!-- Chart -->
    <script src="<?php echo __siteUrl; ?>static/js/chart.min.js"></script>

    <!-- Table -->
    <link href="<?php echo __siteUrl; ?>static/css/bootstrap-table.min.css" rel="stylesheet" />
    <script src="<?php echo __siteUrl; ?>static/js/bootstrap-table.min.js"></script>

    <!-- Quest OJ Manage Platform -->
    <script src="<?php echo __siteUrl; ?>static/js/style.js?ver=2020.02.01"></script>

    <!-- Quest OJ -->
    <script src="<?= OJ_URL ?>/js/qoj.js?v=2019.12.28"></script>
    <script src="<?= OJ_URL ?>/js/readmore/readmore.min.js"></script>
    <script src="<?= OJ_URL ?>/js/color-converter.min.js"></script>
    <script type="text/javascript">uojHome = '<?= OJ_URL ?>'</script>
</head>