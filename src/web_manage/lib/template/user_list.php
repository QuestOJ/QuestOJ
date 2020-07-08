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
    $search_status = $_GET["status"];
    $search_group = $_GET["group"];

    if (isset($_GET["group"]) && (!is_numeric($search_group) || !getGroupInfo($search_group))) {
        header("Location:/404");
        exit;
    }

    if ($search_status == "N") {
        $search_status_trans = "正常";
    } else if ($search_status == "U") {
        $search_status_trans = "未验证";
    } else if ($search_status == "B"){
        $search_status_trans = "已禁用";
    } else if ($search_status == "S"){
        $search_status_trans = "管理员";
    }

    $first = false;

    $sql = "SELECT `username`,`userdefine`,`realname`,`email`,`rating`,`verify`,`usergroup` FROM `user_info`";
    $searchRedirect = "";

    if (isset($_GET["s"])) {
        if (!$first) {
            $sql = $sql." where (`username` like '%$search%' or `realname` like '%$search%' or `email` like '%$search%')";
            $searchRedirect = $searchRedirect."?s=".urlencode($search);
            $first = true;
        } else {
            $sql = $sql." and (`username` like '%$search%' or `realname` like '%$search%' or `email` like '%$search%')";
            $searchRedirect = $searchRedirect."?s=".urlencode($search);
        }        
    } else {
        $search = "";
    }


    if (isset($_GET["group"])) {
        if (!$first) {
            $sql = $sql." where userdefine = '$search_group'";
            $searchRedirect = $searchRedirect."?group=".urlencode($search_group);
            $first = true;
        } else {
            $sql = $sql." and userdefine = '$search_group'";
            $searchRedirect = $searchRedirect."&group=".urlencode($search);
        }        
    } else {
        $search_group = "";
    }

    if ($search_status == "N") {
        if (!$first) {
            $sql = $sql." where (usergroup != 'B' and verify = '1')";
            $searchRedirect = $searchRedirect."?status=".urlencode($search_status);
            $first = true;
        } else {
            $sql = $sql." and (usergroup != 'B' and verify = '1')";
            $searchRedirect = $searchRedirect."?status=".urlencode($search_status);
        } 
    }

    if ($search_status == "B" || $search_status == "S") {
        if (!$first) {
            $sql = $sql." where usergroup = '$search_status'";
            $searchRedirect = $searchRedirect."?status=".urlencode($search_status);
            $first = true;
        } else {
            $sql = $sql." and usergroup = '$search_status'";
            $searchRedirect = $searchRedirect."?status=".urlencode($search_status);
        } 
    }

    if ($search_status == "U") {
        if (!$first) {
            $sql = $sql." where verify = '0'";
            $searchRedirect = $searchRedirect."?status=".urlencode($search_status);
            $first = true;
        } else {
            $sql = $sql." and verify = '0'";
            $searchRedirect = $searchRedirect."?status=".urlencode($search_status);
        } 
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
        header("Location:/user/list/page/{$totalPage}".$searchRedirect);
        exit;
    }
    if($page < 1){
        header("Location:/user/list/page/1".$searchRedirect);
        exit;
    }

    /**
     * 查询数据
     */
    
    $startID = ($page - 1) * $pageSize;
    $dataQuery = db::query("oj", $sql."LIMIT $startID, $pageSize"); 
?>

<?= html::header(); ?>
    <div class="modal fade" id="editModel" tabindex="-1" role="dialog" aria-labelledby="editModal" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form id="form-edit" class="form-horizontal" method="post">
                <div id="div-token">
                    <input type="hidden" id="input-token" value="<?= frame::clientKey() ?>">
                </div>
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">编辑</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div id="div-usergroup" class="from-group mb-1">
                        <label for="input-usergroup" class="col-form-label">管理员:</label>
                        <select class="custom-select" id="input-usergroup">
                            <option id="option-N" value="N">否</option>
                            <option id="option-Y" value="Y">是</option>
                        </select>
                    </div>
                    <div id="div-username" class="form-group mb-1">
                        <label for="input-username" class="col-form-label">用户名:</label>
                        <input type="text" class="form-control" id="input-username" disabled>
                        <span class="help-block" id="help-username"></span>
                    </div>
                    <div id="div-userdefine" class="from-group mb-1">
                        <label for="input-userdefine" class="col-form-label">用户组:</label>
                        <select class="custom-select" id="input-userdefine">
                            <?php
                                $sql = db::query("oj", "select * from usergroup");
                                while ($rows = mysqli_fetch_assoc($sql)) {
                                    echo "<option id=\"option-{$rows["id"]}\"value=\"{$rows["id"]}\">{$rows["name"]}</option>";
                                }
                            ?>
                        </select>
                    </div>
                    <div id="div-realname" class="form-group mb-1">
                        <label for="input-realname" class="col-form-label">姓名:</label>
                        <input type="text" class="form-control" id="input-realname">
                        <span class="help-block" id="help-realname"></span>
                    </div>
                    <div id="div-email" class="form-group mb-1">
                        <label for="input-email" class="col-form-label">邮箱:</label>
                        <input type="text" class="form-control" id="input-email">
                        <span class="help-block" id="help-email"></span>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary" id="button-submit">提交</button>
                </div>
                </form>
            </div>
            <script type="text/javascript">
                function validateEditPost() {
                    var ok = true;
                    ok &= getFormErrorAndShowHelp('username', validateUsername);
                    ok &= getFormErrorAndShowHelp('realname', validateRealname);
                    ok &= getFormErrorAndShowHelp('email', validateEmail);
                    return ok;
                }

                function submitEditPost() {
                    if (!validateEditPost()) {
                        return false;
                    }
                    
                    $.post('/user/edit', {
                        username : $('#input-username').val(),
                        realname : $('#input-realname').val(),
                        email : $('#input-email').val(),
                        userdefine : $('#input-userdefine').val(),
                        usergroup : $('#input-usergroup').val(),
                        token : $('#input-token').val(),
                        action : "edit",
                    }, function(msg) {
                        if (msg == 'ok') {
                            $('#editModel').modal('hide');
                            location.reload();
                        } else if (msg == 'email') {
                            $('#div-email').addClass('has-error');
                            $('#help-email').html('电子邮箱已存在。');
                        } else if (msg == 'expired') {
                            $('#div-username').addClass('has-error');
                            $('#help-username').html('页面会话已过期。');
                        } else if (msg == 'username') {
                            $('#div-username').addClass('has-error');
                            $('#help-username').html('用户不存在。');
                        } else {
                            $('#div-username').addClass('has-error');
                            $('#help-username').html('未知错误。');
                        }
                    });

                    return true;
                }

                $(document).ready(function() {
                    $('#form-edit').submit(function(e) {
                        e.preventDefault();
                        submitEditPost();
                    });
                });
        </script>
        </div>
    </div>

<div class="d-none d-sm-block">
    <nav style="text-align: center; padding:0px 0px 20px 0px;">
        <form id="form-search" class="form-inline" method="get">
                <div id="form-group-search" class="form-group">
                    <label for="input-s" class="control-label">用户信息</label>
                    <input type="text" class="form-control input-sm" name="s" id="input-s" maxlength="50" style="width:25em" value="<?php echo $search; ?>">
                </div>
                <div id="form-group-status" class="form-group">
                <label for="input-status" class="control-label">状态</label>
                <select id="input-status" name="status" class="form-control input-sm">
                    <option selected value="<?= $search_status ?>" hidden><?= $search_status_trans; ?></option>
                    <?php if (!empty($search_status)) {echo "<option></option>";} ?>
                    <option value="N">正常</option>
                    <option value="U">未验证</option>
                    <option value="B">已禁用</option>
                    <option value="S">管理员</option>
                </select>
                </div>
                <div id="form-group-group" class="form-group">
                <label for="input-group" class="control-label">用户组</label>
                <select id="input-group" name="usergroup" class="form-control input-sm">
                    <option selected value="<?= $search_group ?>" hidden><?php if (isset($_GET["group"])) {echo getGroupInfo($search_group)["name"]; } ?></option>
                    <?php if (isset($search_group)) {echo "<option></option>";} ?>
                    <?php
                        $sql = db::query("oj", "select * from usergroup");
                        while ($rows = mysqli_fetch_assoc($sql)) {
                            echo "<option value=\"{$rows["id"]}\">{$rows["name"]}</option>";
                        }
                    ?>
                </select>
                </div>
                <div style="padding: 0 0 0 5px;">
                    <button type="submit" id="submit-search" class="btn btn-default btn-secondary">搜索</button>
                </div>
        </form>

        <script type="text/javascript">
        $('#form-search').submit(function(e) {
            e.preventDefault();
            
            url = '/user/list';
            qs = [];
            $(['s', 'status', 'group']).each(function () {
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
                <th class="text-center">用户名</th>
                <th class="text-center">姓名</th>
                <th class="text-center">邮箱</th>
                <th class="text-center">Rating</th>
                <th class="text-center">用户组</th>
                <th class="text-center">状态</th>
                <th class="text-center">操作</th>
            </tr>
        </thead>
        <tbody>
        <?php
            if ($total == 0) {
                echo "<tr class=\"text-center\"><td colspan=\"8\">暂无数据</td></tr>";
            }
            
            $id = $startID;

            while($dataQueryRow = mysqli_fetch_assoc($dataQuery)){
                $id += 1;

                $username = $dataQueryRow["username"];
                $userdefine = $dataQueryRow["userdefine"];
                $realname = $dataQueryRow["realname"];
                $email = $dataQueryRow["email"];
                $rating = $dataQueryRow["rating"];
                $ac_num = $dataQueryRow["ac_num"];
                $usergroup = $dataQueryRow["usergroup"];
                $verify = $dataQueryRow["verify"];

                if ($usergroup == "S") {
                    echo "<tr class=\"text-center\" style='color:orange'>";
                } else {
                    echo "<tr class=\"text-center\">";
                }
                echo "<td>#{$id}</td>";
                echo "<td><span class=\"uoj-username\" data-rating=\"{$rating}\">{$username}</span></td>";
                echo "<td>{$realname}</td>";
                echo "<td>{$email}</td>";
                echo "<td>{$rating}</td>";
                echo "<td><a href=\"/user/list?group={$userdefine}\">".getGroupInfo($userdefine)["name"]."</a></td>";

                if($usergroup == "B") {
                    echo "<td><b><font color='#DF013A'>已禁用</font></b></td>";
                } else if($verify == 0){
                    echo "<td><b><font color='#DF013A'>未验证</font></b></td>";
                } else {
                    echo "<td><b><font color='#5FB404'>正常</font></b></td>";
                }

                if ($usergroup != "B") {
                    echo '<td><a href = "#" data-toggle="modal" data-target="#editModel" data-username="'.$username.'" data-realname="'.$realname.'" data-email="'.$email.'" data-userdefine="'.$userdefine.'" data-usergroup="'.$usergroup.'"><span class="glyphicon glyphicon-pencil"></span>编辑</a> &nbsp; <a href="javascript:void(0);" onclick="Ban(\''.$username.'\',\''.frame::clientKey().'\')"><span class="glyphicon glyphicon-remove">禁用</a></td>';
                } else {
                    echo '<td><a href = "#" data-toggle="modal" data-target="#editModel" data-username="'.$username.'" data-realname="'.$realname.'" data-email="'.$email.'" data-userdefine="'.$userdefine.'" data-usergroup="'.$usergroup.'"><span class="glyphicon glyphicon-pencil"></span>编辑</a> &nbsp; <a href="javascript:void(0);" onclick="Active(\''.$username.'\',\''.frame::clientKey().'\')"><span class="glyphicon glyphicon-ok">启用</a></td>';
                }
                echo "</tr>";
            }
        ?>
        </tbody>
    </table>
</div>

<nav aria-label="Page navigation">
        <ul class="pagination justify-content-center">
            <li class="page-item">
                <a class="page-link" href="/user/list/page/1<?= $searchRedirect ?>" aria-label="Previous">
                    <span aria-hidden="true">&laquo;</span>
                </a>
            </li>

            <?php
                $cnt = 0;

                if($page - 2 > 0){
                    ++$cnt;
                    echo "<li class=\"page-item\"><a class=\"page-link\" href=\"/user/list/page/".($page-2).$searchRedirect."\">".($page-2)."</a></li>";
                }

                if($page - 1 > 0){
                    ++$cnt;
                    echo "<li class=\"page-item\"><a class=\"page-link\" href=\"/user/list/page/".($page-1).$searchRedirect."\">".($page-1)."</a></li>";
                }
            ?>

            <li class="page-item active"><a class="page-link" href="#"><?php echo $page; ?></a></li>
            
            <?php
                $id = 1;

                for($id; $id + $cnt < 5; ++$id){
                    if($id + $page <= $totalPage){
                        echo "<li class=\"page-item\"><a class=\"page-link\" href=\"/user/list/page/".($page+$id).$searchRedirect."\">".($page+$id)."</a></li>";
                    }
                }
            ?>
            
            <li class="page-item">
                <a class="page-link" href="/user/list/page/<?php echo $totalPage.$searchRedirect; ?>" aria-label="Next">
                <span aria-hidden="true">&raquo;</span>
                </a>
            </li>
        </ul>
    </nav>
    <script>
        function Active(Username, token){
            $.post('/user/edit', {
                token : token,
                username : Username,
                action : "active",
            }, function(msg) {
                if (msg == 'ok') {
                    location.reload();
                }
            });
        }

        function Ban(Username, token){
            $.post('/user/edit', {
                token : token,
                username : Username,
                action : "ban",
            }, function(msg) {
                if (msg == 'ok') {
                    location.reload();
                }
            });
        }

        $('#editModel').on('show.bs.modal', function (event) {
            var button = $(event.relatedTarget)
            var username = button.data('username')
            var realname = button.data('realname')
            var email = button.data('email')
            var userdefine = button.data('userdefine')
            var usergroup = button.data('usergroup')

            var modal = $(this)
            modal.find('.modal-title').text('修改 ' + username + ' 账户信息')
            modal.find('#div-username input').val(username)
            modal.find('#div-realname input').val(realname)
            modal.find('#div-email input').val(email)
            
            document.getElementById("option-"+userdefine).selected=true;

            if (usergroup == "S") {
                document.getElementById("option-Y").selected=true;
            } else {
                document.getElementById("option-N").selected=true;
            }
        })

        $('#editModel').on('hidden.bs.modal', function (event) {
            showErrorHelp('username');
            showErrorHelp('realname');
            showErrorHelp('email');
        })
    </script>
<?= html::footer(); ?>