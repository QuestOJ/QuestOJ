<?php

    if (!defined("load")) {
        header("HTTP/1.1 404 Not Found");
        exit;
    }
	
	if (!empty($_POST["clientID"])) {
        $clientID = $_POST["clientID"];
        $clientSecret = $_POST["clientSecret"];
        $taskID = $_POST["taskID"];
        $jobID = $_POST["jobID"];
        $times = $_POST["times"];
        $status = $_POST["status"];
        $comments = $_POST["comments"];
        $uploadCnt = $_POST["uploadCnt"];

        $client = new Client($clientID, $clientSecret);
        
        $client->setTask($taskID);
        if ($status == "new-task") {
            $client->registerTask($comments);
            output(true, $uploadCnt, "");
        }

        if($status == "finished-success" || $status == "finished-failed") {
            $client->finishTask($status);
            output(true, $uploadCnt, "");
        }
        
        $client->setJob($jobID, $times);
        if($status == "new-job") {
            $client->registerJob($comments);
            output(true, $uploadCnt, "");
        }

        if($status == "success" || $status == "failed") {
            $client->finishTimes($status);
            output(true, $uploadCnt, "");
        }

        if ($status == "stdout" || $status == "stderr") {
            $client->details($status, $comments, $uploadCnt);
            output(true, $uploadCnt, "");
        }
	}
?>