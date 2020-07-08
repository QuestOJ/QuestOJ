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
     * 分页设置
     */

    $pageSize = 10;

    /**
     * 获取搜索信息
     */

    $search = db::escape($_GET["s"]);

    $sql = "SELECT * FROM `submissions` where (`result_error` = 'Judgement Failed')";
    $searchRedirect = "";

    if (isset($_GET["s"])) {
        $sql = $sql." and (`problem_id` like '%$search%' or `submitter` like '%$search%')";
        $searchRedirect = $searchRedirect."?s=".urlencode($search);
        $first = true;     
    } else {
        $search = "";
    }

    /**
     * 获取数据总数
     */

    $totalQuery = db::query("oj", $sql);
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
        header("Location:/submission/fail/page/{$totalPage}".$searchRedirect);
        exit;
    }
    if($page < 1){
        header("Location:/submission/fail/page/1".$searchRedirect);
        exit;
    }
    
    /**
     * 查询数据
     */
    
    $startID = ($page - 1) * $pageSize;
    $dataQuery = db::query("oj", $sql."order by id desc LIMIT $startID, $pageSize"); 
?>

<?= html::header(); ?>
<div class="d-none d-sm-block">
    <nav class="mb-3">
        <form id="form-search" class="form-inline" method="get">
                <div id="form-group-search" class="form-group">
                    <label for="input-s" class="control-label">搜索</label>
                    <input type="text" class="form-control input-sm" name="s" id="input-s" maxlength="50" style="width:25em" value="<?php echo $search; ?>">
                </div>
                <div style="padding: 0 0 0 5px;">
                    <button type="submit" id="submit-search" class="btn btn-default btn-secondary">搜索</button>
                </div>
        </form>
        <script type="text/javascript">
        $('#form-search').submit(function(e) {
            e.preventDefault();
            
            url = '/submission/fail';
            qs = [];
            $(['s']).each(function () {
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
    <table class="table table-bordered table-hover table">
        <thead>
            <tr>
                <th class="text-center">ID</th>
                <th class="text-center">题目</th>
                <th class="text-center">提交者</th>
                <th class="text-center">语言</th>
                <th class="text-center">代码长度</th>
                <th class="text-center">提交时间</th>
                <th class="text-center">评测时间</th>
            </tr>
        </thead>
        <tbody>
        <?php
            if ($total == 0) {
                echo "<tr class=\"text-center\"><td colspan=\"7\">暂无数据</td></tr>";
            }

            while($dataQueryRow = mysqli_fetch_assoc($dataQuery)){
                $id = $dataQueryRow["id"];
                $pid = $dataQueryRow["problem_id"];
                $submitter = $dataQueryRow["submitter"];
                $language = $dataQueryRow["language"];
                $submit_time = $dataQueryRow["submit_time"];
                $judge_time = $dataQueryRow["judge_time"];
                $tot_size = $dataQueryRow["tot_size"];

                echo "<tr class=\"text-center\">";
                echo "<td><a href=\"".OJ_URL."/submission/{$id}\" target=\"_blank\">#{$id}</a></td>";
                echo "<td><a href=\"".OJ_URL."/problem/{$pid}\" target=\"_blank\">#{$pid}. ".getProblemInfo($pid)["title"]."</a></td>";
                echo "<td><a href=\"/user/list?s=".$submitter."\" target=\"_blank\">{$submitter}</a></td>";
                echo "<td>{$language}</td>";

                if ($tot_size < 1024) {
                    echo "<td>{$tot_size}b</td>";
                } else {
                    echo "<td>".sprintf("%.1f",  $tot_size / 1024) . "kb</td>";
                }

                echo "<td>{$submit_time}</td>";
                echo "<td>{$judge_time}</td>";

                echo "</tr>";
            }
        ?>
        </tbody>
    </table>
</div>

<nav aria-label="Page navigation">
        <ul class="pagination justify-content-center">
            <li class="page-item">
                <a class="page-link" href="/submission/fail/page/1<?= $searchRedirect ?>" aria-label="Previous">
                    <span aria-hidden="true">&laquo;</span>
                </a>
            </li>

            <?php
                $cnt = 0;

                if($page - 2 > 0){
                    ++$cnt;
                    echo "<li class=\"page-item\"><a class=\"page-link\" href=\"/submission/fail/page/".($page-2).$searchRedirect."\">".($page-2)."</a></li>";
                }

                if($page - 1 > 0){
                    ++$cnt;
                    echo "<li class=\"page-item\"><a class=\"page-link\" href=\"/submission/fail/page/".($page-1).$searchRedirect."\">".($page-1)."</a></li>";
                }
            ?>

            <li class="page-item active"><a class="page-link" href="#"><?php echo $page; ?></a></li>
            
            <?php
                $id = 1;

                for($id; $id + $cnt < 5; ++$id){
                    if($id + $page <= $totalPage){
                        echo "<li class=\"page-item\"><a class=\"page-link\" href=\"/submission/fail/page/".($page+$id).$searchRedirect."\">".($page+$id)."</a></li>";
                    }
                }
            ?>
            
            <li class="page-item">
                <a class="page-link" href="/submission/fail/page/<?php echo $totalPage.$searchRedirect; ?>" aria-label="Next">
                <span aria-hidden="true">&raquo;</span>
                </a>
            </li>
        </ul>
    </nav>
<?= html::footer(); ?>