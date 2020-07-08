<?php

    if(!defined("load") || !isUserLogin()){
        header("Location:/403");
        exit;
    }

    if($appendFlag == 1){
        
        return true;
    }
    
    
    $db = new db();
    $con = $db->connect();

    /**
     * 解析页面 ID
     */

    if (!empty($_GET['page'])) {
        $page = $_GET['page'];
    } else {
        $page = 1;
    }

    if (isUserAdmin()) {
        $allowHidden = 0;
    } else {
        $allowHidden = 1;
    }
    
    /**
     * 50页分页设置
     */

    $pageSize = 10;

    /**
     * 获取搜索信息
     */

    $uid = $_GET["user_id"];
    $username = $_GET["username"];


	$uid = mysqli_real_escape_string($con, $uid);
    $username = mysqli_real_escape_string($con, $username);

    $first = false;
    $second = false;

    $sql = "SELECT `id`,`username`, `regtime`, `email`, `realname`, `lasttime`, `admin`, `start` FROM `{$db->tablePrefix}_users`";
    $searchRedirect = "";

    if ($uid && is_numeric($uid)) {
        $sql = $sql." where `id` = '$uid'";
        $searchRedirect = $searchRedirect."?user_id=".urlencode($tid);
        $first = true;
    } else {
        $uid = "";
    }

    if (!empty($username)) {
        if (!$first) {
            $sql = $sql." where `username` = '$username'";
            $searchRedirect = $searchRedirect."?username=".urlencode($username);
            $first = true;
        } else {
            $sql = $sql." and `username` = '$username'";
            $searchRedirect = $searchRedirect."&username=".urlencode($username);
        }
    } else {
        $username = "";
    }


    if ($first) {
        $searchRedirect = "&".substr($searchRedirect, 1);
    }

    /**
     * 获取数据总数
     */

    $totalQuery = $db->query($sql);
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
        header("Location:/manage/users?page={$totalPage}".$searchRedirect);
        exit;
    }
    if($page < 1){
        header("Location:/manage/users?page=1".$searchRedirect);
        exit;
    }

    /**
     * 查询数据
     */
    
    $startID = ($page - 1) * $pageSize;
    $dataQuery = $db->query($sql." LIMIT $startID, $pageSize"); 
?>

<div class="hidden-xs">
    <nav style="text-align: center; padding:0px 0px 20px 0px;">
        <form id="form-search" class="form-inline" method="get">
                <div id="form-group-user-id" class="form-group">
                    <label for="input-user_id" class="control-label">用户ID </label>
                    <input type="text" class="form-control input-sm" name="user_id" id="input-user_id" maxlength="6" style="width:6em" value="<?php echo $uid; ?>">
                </div>
                <div id="form-group-username" class="form-group">
                    <label for="input-username" class="control-label">用户名 </label>
                    <input type="text" class="form-control input-sm" name="username" id="input-username" maxlength="15" style="width:10em" value="<?php echo $username; ?>">
                </div>
                <button type="submit" id="submit-search" class="btn btn-default btn-sm">搜索</button>
        </form>

        <script type="text/javascript">
        $('#form-search').submit(function(e) {
            e.preventDefault();
            
            url = '/manage/users';
            qs = [];
            $(['user_id', 'username']).each(function () {
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
                <th class="text-center" style="width:15%;">用户名</th>
                <th class="text-center" style="width:10%;">真实姓名</th>
                <th class="text-center" style="width:20%;">邮箱</th>
                <th class="text-center" style="width:15%;">注册时间</th>
                <th class="text-center" style="width:15%;">上一次登录时间</th>
                <th class="text-center" style="width:10%;">账户类型</th>
                <th class="text-center" style="width:10%;">状态</th>
            </tr>
        </thead>
        <tbody>
        <?php
            if ($total == 0) {
                echo "<tr class=\"text-center\"><td colspan=\"8\">暂无数据</td></tr>";
            }
            
            while($dataQueryRow = mysqli_fetch_assoc($dataQuery)){
                $id = $dataQueryRow["id"];
                $user = $dataQueryRow["username"];
				$realname = $dataQueryRow["realname"];
				$regtime = $dataQueryRow["regtime"];
				$lasttime = $dataQueryRow["lasttime"];
				$email = $dataQueryRow["email"];
				$admin = $dataQueryRow["admin"];
				$start = $dataQueryRow["start"];
				
                echo "<tr class=\"text-center\">";
                echo "<td>{$id}</td>";
                echo "<td>{$user}</td>";
                echo "<td>{$realname}</td>";
                echo "<td>{$email}</td>";
                echo "<td>{$regtime}</td>";
                echo "<td>{$lasttime}</td>";
				
				if ($admin == 1) {
					echo "<td><a href=\"/manage/users/edit_group?id={$id}&group=0\">管理员账户</a></td>";
				} else {
					echo "<td><a href=\"/manage/users/edit_group?id={$id}&group=1\">普通账户</a></td>";
				}
				
				if ($start == 1) {
					echo "<td><a href=\"/manage/users/edit_status?id={$id}&status=0\">已启用</a></td>";
				} else {
					echo "<td><a href=\"/manage/users/edit_status?id={$id}&status=1\">已停用</a></td>";
				}
                echo "</tr>";
            }
        ?>
        </tbody>
    </table>
</div>

<nav aria-label="Page navigation" style="text-align: center">
    <ul class="pagination">
        <li>
            <a href="/manage/users?page=1<?php echo $searchRedirect; ?>" aria-label="Previous">
                <span class="glyphicon glyphicon-fast-backward" aria-hidden="true"></span>
            </a>
        </li>

        <?php
            $cnt = 0;

            if($page - 2 > 0){
                ++$cnt;
                echo "<li><a href=\"/manage/users?page=".($page-2).$searchRedirect."\">".($page-2)."</a></li>";
            }

            if($page - 1 > 0){
                ++$cnt;
                echo "<li><a href=\"/manage/users?page=".($page-1).$searchRedirect."\">".($page-1)."</a></li>";
            }
        ?>

        <li class="active"><a href="#"><?php echo $page; ?></a></li>
        
        <?php
            $id = 1;

            for($id; $id + $cnt < 5; ++$id){
                if($id + $page <= $totalPage){
                    echo "<li><a href=\"/manage/users?page=".($page+$id).$searchRedirect."\">".($page+$id)."</a></li>";
                }
            }
        ?>
        
        <li>
            <a href="/manage/users?page=<?php echo $totalPage; echo $searchRedirect; ?>" aria-label="Next">
                <span class="glyphicon glyphicon-fast-forward" aria-hidden="true"></span>
            </a>
        </li>
    </ul>
</nav>