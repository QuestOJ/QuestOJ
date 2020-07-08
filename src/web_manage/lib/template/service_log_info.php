<?php

if (!defined("load") || !isUserLogin()) {
    header("Location:/403");
    exit;
}

/**
 * 解析页面 ID
 */

$pregResult = preg_match_all("/\d+/", $_SERVER['REQUEST_URI'], $pageArray);

if (!$pregResult) {
    $ID = 0;
} else {
    $ID = $pageArray[0][0];
}

$taskInfo = getTaskInfo($ID);

if (!$taskInfo) {
    header("Location:/404");
    exit;
}

$taskID = $taskInfo["taskid"];
$cid = $taskInfo["cid"];

$sql = "SELECT * FROM `".MYSQL_TABLE_PREFIX."_task_job` where `taskID` = '$taskID' and `cid` = '$cid'";
$dataQuery = db::query("local", $sql);

$result = array();
$jobResult = array();
$tmpResult = array();
$lastJob = 0;
$index = 0;

while ($dataQueryRow = mysqli_fetch_assoc($dataQuery)) {
    if ($dataQueryRow["jobid"] != $lastJob) {
        array_push($result, $tmpResult);

        $lastJob = $dataQueryRow["jobid"];
        $tmpResult = array($dataQueryRow);

        $index = $index + 1;
        array_push($jobResult, array($index, $dataQueryRow["jobid"], $dataQueryRow["name"]));
    } else {
        array_push($tmpResult, $dataQueryRow);
    }
}

array_push($result, $tmpResult);
?>

<?= html::header() ?>
<div class="modal fade bd-example-modal-lg" id="infoModel" tabindex="-1" role="dialog" aria-labelledby="infoModel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">详情</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <ul class="nav nav-tabs" id="infoTab" role="tablist">
                            <li class='nav-items'><a class='nav-link active' href='#stdout' id='tab-stdout' aria-controls='stdout' role='tab' data-toggle='tab'>stdout</a></li>
                            <li class='nav-items'><a class='nav-link' href='#stderr' id='tab-stderr' aria-controls='stderr' role='tab' data-toggle='tab'>stderr</a></li>
                        </ul>
                    </div>
                    <div class="tab-content">
                        <div role="tabpanel" class="tab-pane active" id="stdout">
                            <div class="card">
                                <div class="card-body" style="white-space: pre-line;"><code id="code-stdout"></code></div>
                            </div>
                        </div>
                        <div role="tabpanel" class="tab-pane" id="stderr">
                        <div class="card">
                                <div class="card-body" style="white-space: pre-line;"><code id="code-stderr"></code></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
<div class="mb-2">
    <h3>任务信息</h3>
</div>

<div class="table-responsive">
    <table class="table table-bordered table-hover">
        <thead>
            <tr>
                <th class="text-center" style="width:5%;">ID</th>
                <th class="text-center" style="width:10%;">客户端ID</th>
                <th class="text-center" style="width:20%;">任务ID</th>
                <th class="text-center" style="width:25%;">任务名</th>
                <th class="text-center" style="width:10%;">任务结果</th>
                <th class="text-center" style="width:15%;">启动时间</th>
                <th class="text-center" style="width:15%;">结束时间</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $id = $taskInfo["id"];
            $cid = $taskInfo["cid"];
            $tid = $taskInfo["taskid"];
            $name = $taskInfo["name"];
            $status = $taskInfo["status"];
            $startTime = $taskInfo["startTime"];
            $endTime = $taskInfo["endTime"];
            $description = $taskInfo["description"];

            echo "<tr class=\"text-center\">";
            echo "<td><a href=\"/service/log/info/{$id}\">#{$id}</a></td>";
            echo "<td><a href=\"/service/client/info/{$cid}\">#client{$cid}</a></td>";
            echo "<td>{$tid}</td>";
            echo "<td>{$name}</td>";

            if ($status == "running") {
                echo "<td><b><font color='#084B8A'>Running</font></b></td>";
            } else if ($status == "failed") {
                echo "<td><b><font color='#DF013A'>Failed</font></b></td>";
            } else if ($status == "success") {
                echo "<td><b><font color='#5FB404'>Success</font></b></td>";
            }

            echo "<td>{$startTime}</td>";
            echo "<td>{$endTime}</td>";
            echo "</tr>";
            ?>
        </tbody>
    </table>
    </div>

    <div class="mb-2">
        <h3>任务描述</h3>
    </div>

    <div class="card mb-3">
        <div class="card-body">
            <?php if(!empty($description)) echo $description; else echo "无任务描述"; ?><br/>
        </div>
    </div>

    <div class="mb-2">
        <h3>子任务信息</h3>
    </div>

    <div style="padding:0px 0px 10px 0px;">
        <ul class="nav nav-tabs" id="myTab" role="tablist">
            <?php
            $active = false;

            foreach ($jobResult as $jobs) {
                $jobid = $jobs[1];
                $name = $jobs[2];

                if (!$active) {
                    $active = true;
                    echo "<li class='nav-items'><a class='nav-link active' href='#job-{$jobid}' aria-controls='job-{$jobid}' role='tab' data-toggle='tab'>{$name}</a></li>";
                } else {
                    echo "<li class='nav-items'><a class='nav-link' href='#job-{$jobid}' aria-controls='job-{$jobid}' role='tab' data-toggle='tab'>{$name}</a></li>";
                }
                
            }
            ?>
        </ul>
    </div>

<div class="table-responsive">
    <!-- Tab panes -->
    <div class="tab-content">
        <?php
        $active = false;

        foreach ($jobResult as $jobs) {
            $jobid = $jobs[1];

            if ($jobid != 0) {

                if (!$active) {
                    $active = true;
                    echo "<div role=\"tabpanel\" class=\"tab-pane active\" id=\"job-{$jobid}\">";
                } else {
                    echo "<div role=\"tabpanel\" class=\"tab-pane\" id=\"job-{$jobid}\">";
                }

                echo '
                <table class="table table-bordered table-hover">
                <thead>
                    <tr>
                        <th class="text-center" style="width:5%;">ID</th>
                        <th class="text-center" style="width:20%;">任务ID</th>
                        <th class="text-center" style="width:10%;">子任务ID</th>
                        <th class="text-center" style="width:15%;">运行ID</th>
                        <th class="text-center" style="width:10%;">状态</th>
                        <th class="text-center" style="width:20%;">执行时间</th>
                        <th class="text-center" style="width:10%;">详情</th>
                    </tr>
                </thead>
                <tbody>
                    ';

                $id = 0;

                foreach ($result[$jobs[0]] as $info) {
                    echo "<tr class=\"text-center\">";

                    $id = $id + 1;
                    $realID = $info["id"];
                    $taskid = $info["taskid"];
                    $startTime = $info["startTime"];
                    $endTime = $info["endTime"];
                    $jobID = $info["jobid"];
                    $attempt = $info["attempt"];
                    $status = $info["status"];
                    $description = $info["description"];

                    echo "<td>{$id}</td>";
                    echo "<td>{$taskid}</td>";
                    echo "<td>Job #{$jobID}</td>";
                    echo "<td>Run #{$attempt}</td>";

                    if ($status == "running") {
                        echo "<td><b><font color='#084B8A'>Running</font></b></td>";
                    } else if ($status == "failed") {
                        echo "<td><b><font color='#DF013A'>Failed</font></b></td>";
                    } else if ($status == "success") {
                        echo "<td><b><font color='#5FB404'>Success</font></b></td>";
                    }

                    echo "<td>{$startTime}</td>";

                    echo '<td><a href="#"  data-toggle="modal" data-target="#infoModel" data-taskid="'.$tid.'" data-jobid="'.$jobid.'" data-attempt="'.$attempt.'" data-cid="'.$cid.'">详情</a></td>';
                    
                    echo "</tr>";
                }

                echo '
                </tbody>
                </table>
                ';

                echo "</div>";
            }
        }
        ?>
    </div>
</div>

<script>
    var handle
    var stdout
    var stderr

    function fetchInfo(clientid, taskid, jobid, attempt) {
        $.ajax({
            url: "/service/log/detail",
            data: {
                clientid: clientid,
                taskid: taskid,
                jobid: jobid,
                attempt: attempt,
                stdout: stdout,
                stderr: stderr,
                token: '<?= frame::clientKey() ?>'
            },
            type: "POST",
            dataType: "json",
            success: function(data) {
                data.stdout.forEach(function(res) {
                    stdout = res[0]
                    comments = decodeURIComponent(escape(window.atob(res[1])))

                    console.log(comments)

                    var html = document.getElementById("code-stdout").innerHTML;
                    document.getElementById("code-stdout").innerHTML = html + comments;
                })

                data.stderr.forEach(function(res) {
                    stderr = res[0]
                    comments = decodeURIComponent(escape(window.atob(res[1])))

                    console.log(comments)

                    var html = document.getElementById("code-stderr").innerHTML;
                    document.getElementById("code-stderr").innerHTML = html + comments;
                })
            }
        })
    }

    $('#infoModel').on('show.bs.modal', function (event) {
        var button = $(event.relatedTarget)
        
        var clientid = button.data('cid')
        var taskid = button.data('taskid')
        var jobid = button.data('jobid')
        var attempt = button.data('attempt')

        var modal = $(this)
        modal.find('.modal-title').text('执行信息 '+clientid+'-'+taskid+'-'+jobid+'-'+attempt)

        stdout = 0
        stderr = 0

        document.getElementById("code-stdout").innerHTML = ""
        document.getElementById("code-stderr").innerHTML = ""

        $('#tab-stderr').removeClass("active")
        $('#tab-stdout').addClass("active")
        $('#stderr').removeClass("active")
        $('#stdout').addClass("active")

        fetchInfo(clientid, taskid, jobid, attempt)

        handle = setInterval(function () {
            fetchInfo(clientid, taskid, jobid, attempt)
        }, 3000)
    });

    $('#infoModel').on('hidden.bs.modal', function (event) {
        clearInterval(handle)
    })
</script>
<?= html::footer(); ?>