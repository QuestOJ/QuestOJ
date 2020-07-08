<?php

    if (!defined("load") || !isUserLogin()) {
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
     * 30页分页设置
     */

    $pageSize = 30;

    /**
     * 获取搜索信息
     */

    $search_tid = $_GET["tid"];
    $search_cid = $_GET["cid"];
    $search_name = $_GET["name"];
    $search_status = $_GET["status"];

    $search_name = db::escape($search_name);
    $search_status = db::escape($search_status);

    $first = false;

    $sql = "SELECT `id`,`cid`,`taskid`,`name`,`status`,`startTime`,`endTime` FROM `".MYSQL_TABLE_PREFIX."_task`";
    $searchRedirect = "";

    if ($search_tid && is_numeric($search_tid)) {
        if (!$first) {
            $sql = $sql." where `taskid` = '$search_tid'";
            $searchRedirect = $searchRedirect."?tid=".urlencode($search_tid);
            $first = true;
        } else {
            $sql = $sql." and `taskid` = '$search_tid'";
            $searchRedirect = $searchRedirect."&tid=".urlencode($search_tid);            
        }
    } else {
        $search_tid = "";
    }

    if ($search_cid && is_numeric($search_cid)) {
        if (!$first) {
            $sql = $sql." where `cid` = '$search_cid'";
            $searchRedirect = $searchRedirect."?cid=".urlencode($search_cid);
            $first = true;
        } else {
            $sql = $sql." and `cid` = '$search_cid'";
            $searchRedirect = $searchRedirect."&cid=".urlencode($search_cid);            
        }
    } else {
        $search_cid = "";
    }

    if (!empty($search_name)) {
        if (!$first) {
            $sql = $sql." where `name` = '$search_name'";
            $searchRedirect = $searchRedirect."?name=".urlencode($search_name);
            $first = true;
        } else {
            $sql = $sql." and `name` = '$search_name'";
            $searchRedirect = $searchRedirect."&name=".urlencode($search_name);
        }
    } else {
        $search_name = "";
    }

    if (!empty($search_status)) {
        if (!$first) {
            $sql = $sql." where `status` = '$search_status'";
            $searchRedirect = $searchRedirect."?status=".urlencode($search_status);
            $first = true;
        } else {
            $sql = $sql." and `status` = '$search_status'";
            $searchRedirect = $searchRedirect."&status=".urlencode($search_status);
        }        
    } else {
        $search_status = "";
    }

    /**
     * 获取数据总数
     */

    $totalQuery = db::query("local", $sql);
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
        header("Location:/service/log/page/{$totalPage}".$searchRedirect);
        exit;
    }
    if($page < 1){
        header("Location:/service/log/page/1".$searchRedirect);
        exit;
    }

    /**
     * 查询数据
     */
    
    $startID = ($page - 1) * $pageSize;
    $dataQuery = db::query("local", $sql."ORDER BY `id` DESC LIMIT $startID, $pageSize"); 
?>

<?= html::header(); ?>
<div class="d-none d-sm-block">
    <nav style="text-align: center; padding:0px 0px 20px 0px;">
        <form id="form-search" class="form-inline" method="get">
                <div id="form-group-tid" class="form-group">
                    <label for="input-tid" class="control-label">任务ID </label>
                    <input type="text" class="form-control input-sm" name="tid" id="input-tid" maxlength="16" style="width:13em" value="<?php echo $search_tid; ?>">
                </div>
                <div id="form-group-cid" class="form-group">
                    <label for="input-cid" class="control-label">客户端ID </label>
                    <input type="text" class="form-control input-sm" name="cid" id="input-cid" maxlength="5" style="width:6em" value="<?php echo $search_cid; ?>">
                </div>
                <div id="form-group-name" class="form-group">
                    <label for="input-name" class="control-label">任务名 </label>
                    <input type="text" class="form-control input-sm" name="name" id="input-name" maxlength="25" style="width:12m" value="<?php echo $search_name; ?>">
                </div>
                <div id="form-group-status" class="form-group">
                <label for="input-status" class="control-label">任务结果</label>
                <select id="input-status" name="status" class="form-control input-sm">
                    <option selected hidden><?php echo $search_status; ?></option>
                    <?php if (!empty($search_status)) {echo "<option></option>";} ?>
                    <option>Running</option>
                    <option>Failed</option>
                    <option>Success</option>
                </select>
                </div>
                <div style="padding: 0 0 0 5px;">
                    <button type="submit" id="submit-search" class="btn btn-default btn-secondary">搜索</button>
                </div>
        </form>

        <script type="text/javascript">
        $('#form-search').submit(function(e) {
            e.preventDefault();
            
            url = '/service/log';
            qs = [];
            $(['tid', 'cid', 'name', 'status']).each(function () {
                if ($('#input-' + this).val()) {
                    qs.push(this + '=' + encodeURIComponent($('#input-' + this).val()));
                }
            });
            if (qs.length > 0) {
                url += '?' + qs.join('&');
            }
            location.href = url;
        });
        </script>
    </nav>
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
            if ($total == 0) {
                echo "<tr class=\"text-center\"><td colspan=\"7\">暂无数据</td></tr>";
            }
            
            while($dataQueryRow = mysqli_fetch_assoc($dataQuery)){
                $id = $dataQueryRow["id"];
                $cid = $dataQueryRow["cid"];
                $tid = $dataQueryRow["taskid"];
                $name = $dataQueryRow["name"];
                $status = $dataQueryRow["status"];
                $startTime = $dataQueryRow["startTime"];
                $endTime = $dataQueryRow["endTime"];

                echo "<tr class=\"text-center\">";
                echo "<td><a href=\"/service/log/info/{$id}\">#{$id}</a></td>";
                echo "<td><a href=\"#\">#client{$cid}</a></td>";
                echo "<td>{$tid}</td>";
                echo "<td>{$name}</td>";

                if($status == "running") {
                    echo "<td><b><font color='#084B8A'>Running</font></b></td>";
                } else if($status == "failed") {
                    echo "<td><b><font color='#DF013A'>Failed</font></b></td>";
                } else if($status == "success") {
                    echo "<td><b><font color='#5FB404'>Success</font></b></td>";
                }

                echo "<td>{$startTime}</td>";
                echo "<td>{$endTime}</td>";
                echo "</tr>";
            }
        ?>
        </tbody>
    </table>
</div>

<nav aria-label="Page navigation">
        <ul class="pagination justify-content-center">
            <li class="page-item">
                <a class="page-link" href="/service/log/page/1<?= $searchRedirect ?>" aria-label="Previous">
                    <span aria-hidden="true">&laquo;</span>
                </a>
            </li>

            <?php
                $cnt = 0;

                if($page - 2 > 0){
                    ++$cnt;
                    echo "<li class=\"page-item\"><a class=\"page-link\" href=\"/service/log/page/".($page-2).$searchRedirect."\">".($page-2)."</a></li>";
                }

                if($page - 1 > 0){
                    ++$cnt;
                    echo "<li class=\"page-item\"><a class=\"page-link\" href=\"/service/log/page/".($page-1).$searchRedirect."\">".($page-1)."</a></li>";
                }
            ?>

            <li class="page-item active"><a class="page-link" href="#"><?php echo $page; ?></a></li>
            
            <?php
                $id = 1;

                for($id; $id + $cnt < 5; ++$id){
                    if($id + $page <= $totalPage){
                        echo "<li class=\"page-item\"><a class=\"page-link\" href=\"/service/log/page/".($page+$id).$searchRedirect."\">".($page+$id)."</a></li>";
                    }
                }
            ?>
            
            <li class="page-item">
                <a class="page-link" href="/service/log/page/<?php echo $totalPage.$searchRedirect; ?>" aria-label="Next">
                <span aria-hidden="true">&raquo;</span>
                </a>
            </li>
        </ul>
    </nav>