<?php

	if (!defined("load") || !isUserLogin()) {
		header("Location:/403");
		exit;
	}

	if (!auth::checkToken()) {
		exit("expired");
    }
    
    $clientid = $_POST["clientid"];
    $taskid = $_POST["taskid"];
    $jobid = $_POST["jobid"];
    $attempt = $_POST["attempt"];
    $stdout = $_POST["stdout"];
    $stderr = $_POST["stderr"];

    if (!is_numeric($clientid)) {
        exit();
    }

    if (!is_numeric($taskid)) {
        exit();
    }

    if (!is_numeric($jobid)) {
        exit();
    }

    if (!is_numeric($attempt)) {
        exit();
    }

    if (!is_numeric($stdout)) {
        exit();
    }

    if (!is_numeric($stderr)) {
        exit();
    }

    $stdout_assoc = mysqli_fetch_all(db::query("local", "SELECT uploadIndex, comments FROM `".MYSQL_TABLE_PREFIX."_task_online` where cid = $clientid and taskid = $taskid and jobid = $jobid and attempt = $attempt and uploadIndex > $stdout and type = 1"));

    $stderr_assoc = mysqli_fetch_all(db::query("local", "SELECT uploadIndex, comments FROM `".MYSQL_TABLE_PREFIX."_task_online` where cid = $clientid and taskid = $taskid and jobid = $jobid and attempt = $attempt and uploadIndex > $stderr and type = 2"));

    $array = array(
        "stdout" => $stdout_assoc,
        "stderr" => $stderr_assoc
    );
    
    exit(json_encode($array));
?>