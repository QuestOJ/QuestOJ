<?php

    if(!defined("load") || !isUserLogin()){
        header("Location:/403");
        exit;
    }
    
    /**
     * 解析页面 ID
     */

    $pregResult = preg_match_all("/\/page\/(\d+)/", $_SERVER['REQUEST_URI'], $pageArray);

    if(!$pregResult){
        $page = 1;
    }else{
        $page = $pageArray[1][0];
    }

    /**
     * 分页设置
     */

    $pageSize = 15;

    /**
     * 获取数据总数
     */

    $totalQuery = db::query("local", "SELECT * FROM `".MYSQL_TABLE_PREFIX."_logs`");
    $total = mysqli_num_rows($totalQuery);

    /**
     * 计算页面总数
     */

    $totalPage = floor($total / $pageSize);
    
    if($total % $pageSize != 0){
        $totalPage = $totalPage + 1;
    }

    $totalPage = max($totalPage, 1);
    
    /**
     * 非法页面ID跳转
     */

    if($page != 1 && $page > $totalPage){
        header("Location:/manage/log/page/{$totalPage}");
        exit;
    }
    if($page < 1){
        header("Location:/manage/log/page/1");
        exit;
    }

    /**
     * 查询数据
     */
    
    $startID = ($page - 1) * $pageSize;
    $dataQuery = db::query("local", "SELECT * FROM `".MYSQL_TABLE_PREFIX."_logs` ORDER BY `id` DESC LIMIT $startID, $pageSize");    
?>

<?= html::header(); ?>
<div>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="#">首页</a></li>
            <li class="breadcrumb-item active" aria-current="page">系统日志</li>
        </ol>
    </nav>
</div>
<div class="table-responsive">
    <table class="table table-bordered table-hover">
        <thead>
            <tr>
                <th class="text-center" style="width:6%;">ID</th>
                <th class="text-center" style="width:15%;">日期</th>
                <th class="text-center" style="width:6%;">等级</th>
                <th class="text-center" style="width:8%;">事件代码</th>
                <th class="text-center" style="">事件信息</th>
                <th class="text-center" style="width:15%;">请求标识符</th>
                <th class="text-center" style="width:6%;">详情</th>
            </tr>
        </thead>
        <tbody>
        <?php
            while($dataQueryRow = mysqli_fetch_assoc($dataQuery)){
                $logID = $dataQueryRow['id'];
                $logLevel = $dataQueryRow['logLevel'];
                $logCode = $dataQueryRow['logCode'];
                $logText = frame::twoWayDecryption($dataQueryRow['logText'], AUTH_KEY, LOG_SALT);
                $logHash = substr($dataQueryRow['logHash'], 0, 16);
                $logDate = $dataQueryRow['date'];
                
                if($logLevel == 1){
                    echo "<tr class=\"text-center table-danger\">";
                    $logLevel = "Error";
                }else if($logLevel == 2){
                    echo "<tr class=\"text-center table-warning\">";
                    $logLevel = "Warning";
                }else{
                    echo "<tr class=\"text-center table-info\">";
                    $logLevel = "Info";
                }

                echo "<td style=\"vertical-align: middle;\">{$logID}</td>";
                echo "<td style=\"vertical-align: middle;\">{$logDate}</td>";
                echo "<td style=\"vertical-align: middle;\">{$logLevel}</td>";
                echo "<td style=\"vertical-align: middle;\">{$logCode}</td>";
                echo "<td style=\"vertical-align: middle;\">{$logText}</td>";
                echo "<td style=\"vertical-align: middle;\">{$logHash}</td>";
                echo "<td style=\"vertical-align: middle;\"><a href=\"/manage/log/info/{$logID}\">详情</a></td>";
                echo "</tr>";
            }
        ?>
        </tbody>
    </table>
</div>

    <nav aria-label="Page navigation">
        <ul class="pagination justify-content-center">
            <li class="page-item">
                <a class="page-link" href="/manage/log/page/1" aria-label="Previous">
                    <span aria-hidden="true">&laquo;</span>
                </a>
            </li>

            <?php
                $cnt = 0;

                if($page - 2 > 0){
                    ++$cnt;
                    echo "<li class=\"page-item\"><a class=\"page-link\" href=\"/manage/log/page/".($page-2)."\">".($page-2)."</a></li>";
                }

                if($page - 1 > 0){
                    ++$cnt;
                    echo "<li class=\"page-item\"><a class=\"page-link\" href=\"/manage/log/page/".($page-1)."\">".($page-1)."</a></li>";
                }
            ?>

            <li class="page-item active"><a class="page-link" href="#"><?php echo $page; ?></a></li>
            
            <?php
                $id = 1;

                for($id; $id + $cnt < 5; ++$id){
                    if($id + $page <= $totalPage){
                        echo "<li class=\"page-item\"><a class=\"page-link\" href=\"/manage/log/page/".($page+$id)."\">".($page+$id)."</a></li>";
                    }
                }
            ?>
            
            <li class="page-item">
                <a class="page-link" href="/manage/log/page/<?php echo $totalPage; ?>" aria-label="Next">
                <span aria-hidden="true">&raquo;</span>
                </a>
            </li>
        </ul>
    </nav>
<?= html::footer(); ?>