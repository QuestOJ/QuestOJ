<?php

    if (!defined("load")) {
        header("HTTP/1.1 404 Not Found");
        exit;
    }

    class Client {
        var $clientID;
        var $taskID;
        var $jobID;
        var $attempt;

        public function __construct($clientID, $clientSecret) {
            $clientSecretEncode = md5($clientID.$clientSecret);

            $clientQuery = db::query("SELECT * FROM `".MYSQL_TABLE_PREFIX."_client` where `clientID` = '$clientID' and `clientSecret` = '$clientSecretEncode' LIMIT 1");
            $clientQueryResult = db::num_rows("SELECT * FROM `".MYSQL_TABLE_PREFIX."_client` where `clientID` = '$clientID' and `clientSecret` = '$clientSecretEncode' LIMIT 1");

            if (!$clientQueryResult) {
                output(false, "", "Invailed clientID or clientSecret");
            }

            while($clientQueryRow = mysqli_fetch_assoc($clientQuery)){
                $this->clientID = $clientQueryRow["id"];
                $start = $clientQueryRow["start"];

                if ($start != 1) {
                    output(false, "", "Account is disabled");
                }
            }

            $date = date("Y-m-d H:i:s");
            db::query("UPDATE `".MYSQL_TABLE_PREFIX."_client` set `lastUpdate` = '$date' where `id` = '$this->cid'");
        }

        public function setTask($id) {
            $this->taskID = $id;
            $date = date("Y-m-d H:i:s");

            if(!db::num_rows("SELECT * FROM `".MYSQL_TABLE_PREFIX."_task` where `cid` = '$this->clientID' and `taskid` = '$id' LIMIT 1")){
                db::query($sql="insert into `".MYSQL_TABLE_PREFIX."_task` (`cid`,`taskid`,`status`, `startTime`) VALUES('$this->clientID', '$id', 'running', '$date')");

                if (!db::insert_id()) {
                    throw new Error("Insert failed *".$sql);
                }

                db::commit();
            }
        }

        public function registerTask($comments) {
            $arr = json_decode(base64_decode($comments), true);

            $res = db::query($sql = "update `".MYSQL_TABLE_PREFIX."_task` SET name = '{$arr["name"]}', description = '{$arr["description"]}' where cid = '$this->clientID' and taskid = '$this->taskID'");

            if (!$res) {
                throw new Error("Update failed *".$sql);
            }
        }

        public function setJob($id, $times) {
            $this->jobID = $id;
            $this->attempt = $times;

            $date = date("Y-m-d H:i:s");

            if(!db::num_rows("SELECT * FROM `".MYSQL_TABLE_PREFIX."_task_job` where `cid` = '$this->clientID' and `taskid` = '$this->taskID' and `jobid` = '$this->jobID' and `attempt` = '$this->attempt' LIMIT 1")){
                db::query($sql="insert into `".MYSQL_TABLE_PREFIX."_task_job` (`cid`,`taskid`,`jobid`,`attempt`,`status`, `startTime`) VALUES('$this->clientID', '$this->taskID', '$id', '$times', 'running', '$date')");

                if (!db::insert_id()) {
                    throw new Error("Insert failed *".$sql);
                }

                db::commit();
            }
        }

        public function registerJob($comments) {
            $arr = json_decode(base64_decode($comments), true);
            $res = db::query($sql = "update `".MYSQL_TABLE_PREFIX."_task_job` SET name = '{$arr["name"]}', description = '{$arr["description"]}' where cid = '$this->clientID' and taskid = '$this->taskID' and `jobid` = '$this->jobID' and `attempt` = '$this->attempt'");

            if (!$res) {
                throw new Error("Update failed *".$sql);
            }            
        }

        public function details($type, $comments, $index) {
            if ($type == "stdout") {
                $typeA = 1;
            } else if ($type == "stderr") {
                $typeA = 2;
            } else {
                output(false, "", "Unknown details type");
            }

            db::query($sql="insert into `".MYSQL_TABLE_PREFIX."_task_online` (`cid`,`taskid`,`jobid`,`attempt`,`uploadIndex`,`type`,`comments`) VALUES ('$this->clientID', '$this->taskID', '$this->jobID', '$this->attempt', '$index', '$typeA', '$comments')");

            if (!db::insert_id()) {
                throw new Error("Insert failed *".$sql);
            }
            
            db::commit();
        }

        public function finishTimes($status) {
            $date = date('Y-m-d H:i:s');
            $res = db::query($sql = "update `".MYSQL_TABLE_PREFIX."_task_job` SET status = '$status', endTime = '$date' where cid = '$this->clientID' and taskid = '$this->taskID' and `jobid` = '$this->jobID' and `attempt` = '$this->attempt'");

            if (!$res) {
                throw new Error("Update failed *".$sql);
            }
        }

        public function finishTask($status) {
            $date = date('Y-m-d H:i:s');

            if ($status == "finished-success") {
                $status = "success";
            } else if ($status == "finished-failed") {
                $status = "failed";
            }

            $res = db::query($sql = "update `".MYSQL_TABLE_PREFIX."_task` SET status = '$status', endTime = '$date' where cid = '$this->clientID' and taskid = '$this->taskID'");

            if (!$res) {
                throw new Error("Update failed *".$sql);
            }
        }
    }

?> 