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

    $sql = "SELECT * FROM `usergroup`";
    $searchRedirect = "";

    if (isset($_GET["s"])) {
        $sql = $sql." where (`name` like '%$search%' or `comments` like '%$search%')";
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
        header("Location:/user/group/page/{$totalPage}".$searchRedirect);
        exit;
    }
    if($page < 1){
        header("Location:/user/group/page/1".$searchRedirect);
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
                <div id="div-id">
                    <input type="hidden" id="input-id" value="">
                </div>
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">编辑</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div id="div-groupname" class="form-group mb-1">
                        <label for="input-groupname" class="col-form-label">用户组名:</label>
                        <input type="text" class="form-control" id="input-groupname">
                        <span class="help-block" id="help-groupname"></span>
                    </div>
                    <div id="div-comments" class="form-group mb-1">
                        <label for="input-comments" class="col-form-label">备注:</label>
                        <textarea class="form-control" id="input-comments" aria-label="With textarea" rows="3"></textarea>
                        <span class="help-block" id="help-comments"></span>
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
                    ok &= getFormErrorAndShowHelp('groupname', validateGroupname);
                    return ok;
                }

                function submitEditPost() {
                    if (!validateEditPost()) {
                        return false;
                    }
                    
                    $.post('/user/group/edit', {
                        id : $('#input-id').val(),
                        groupname : $('#input-groupname').val(),
                        comments : $('#input-comments').val(),
                        token : $('#input-token').val(),
                        action : "edit",
                    }, function(msg) {
                        if (msg == 'ok') {
                            $('#editModel').modal('hide');
                            location.reload();
                        } else if (msg == 'groupname') {
                            $('#div-groupname').addClass('has-error');
                            $('#help-groupname').html('用户组名已存在。');
                        } else if (msg == 'expired') {
                            $('#div-groupname').addClass('has-error');
                            $('#help-groupname').html('页面会话已过期。');
                        } else {
                            $('#div-groupname').addClass('has-error');
                            $('#help-groupname').html('未知错误。');
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
    <div class="modal fade" id="addModel" tabindex="-1" role="dialog" aria-labelledby="addModel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form id="form-add" class="form-horizontal" method="post">
                <div id="div-add-token">
                    <input type="hidden" id="input-add-token" value="<?= frame::clientKey() ?>">
                </div>
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">编辑</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div id="div-add-groupname" class="form-group mb-1">
                        <label for="input-add-groupname" class="col-form-label">用户组名:</label>
                        <input type="text" class="form-control" id="input-add-groupname">
                        <span class="help-block" id="help-add-groupname"></span>
                    </div>
                    <div id="div-add-comments" class="form-group mb-1">
                        <label for="input-add-comments" class="col-form-label">备注:</label>
                        <textarea class="form-control" id="input-add-comments" aria-label="With textarea" rows="3"></textarea>
                        <span class="help-block" id="help-add-comments"></span>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary" id="button-add-submit">提交</button>
                </div>
                </form>
            </div>
            <script type="text/javascript">
                function validateAddPost() {
                    var ok = true;
                    ok &= getFormErrorAndShowHelp('add-groupname', validateGroupname);
                    return ok;
                }

                function submitAddPost() {
                    if (!validateAddPost()) {
                        return false;
                    }
                    
                    $.post('/user/group/edit', {
                        groupname : $('#input-add-groupname').val(),
                        comments : $('#input-add-comments').val(),
                        token : $('#input-add-token').val(),
                        action : "add",
                    }, function(msg) {
                        if (msg == 'ok') {
                            $('#addModel').modal('hide');
                            location.reload();
                        } else if (msg == 'groupname') {
                            $('#div-add-groupname').addClass('has-error');
                            $('#help-add-groupname').html('用户组名已存在。');
                        } else if (msg == 'expired') {
                            $('#div-add-groupname').addClass('has-error');
                            $('#help-add-groupname').html('页面会话已过期。');
                        } else {
                            $('#div-add-groupname').addClass('has-error');
                            $('#help-add-groupname').html('未知错误。');
                        }
                    });

                    return true;
                }

                $(document).ready(function() {
                    $('#form-add').submit(function(e) {
                        e.preventDefault();
                        submitAddPost();
                    });
                });
        </script>
        </div>
    </div>
    <div class="modal fade" id="confirmModel" tabindex="-1" role="dialog" aria-labelledby="confirmModelLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="confirmModalLabel">Modal title</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                您确认要执行此操作吗
            </div>
            <form id="form-confirm" class="form-horizontal" method="post">
            
            <input type="hidden" id="input-confirm-token" value="<?= frame::clientKey() ?>">
            <input type="hidden" id="input-confirm-action" value="">
            <input type="hidden" id="input-confirm-groupname" value="">
            <input type="hidden" id="input-confirm-id" value="">

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">关闭</button>
                <button type="submit" class="btn btn-primary">确定</button>
            </div>
            </form>
            </div>
            <script type="text/javascript">
                function submitConfirmPost() {
                    $.post('/user/group/edit', {
                        groupname : $('#input-confirm-groupname').val(),
                        id : $('#input-confirm-id').val(),
                        token : $('#input-confirm-token').val(),
                        action : $('#input-confirm-action').val(),
                    }, function(msg) {
                        if (msg == 'ok') {
                            $('#confirmModel').modal('hide');
                            location.reload();
                        }
                    });

                    return true;
                }

                $(document).ready(function() {
                    $('#form-confirm').submit(function(e) {
                        e.preventDefault();
                        submitConfirmPost();
                    });
                });
        </script>
        </div>
    </div>
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
                <div style="padding: 0 0 0 5px;">
                    <button type="button" class="btn btn-default btn-primary" data-toggle="modal" data-target="#addModel" >新增用户组</button>
                </div>
        </form>
        <script type="text/javascript">
        $('#form-search').submit(function(e) {
            e.preventDefault();
            
            url = '/user/group';
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
                <th class="text-center">用户组</th>
                <th class="text-center">备注</th>
                <th class="text-center">用户数</th>
                <th class="text-center">操作</th>
            </tr>
        </thead>
        <tbody>
        <?php
            if ($total == 0) {
                echo "<tr class=\"text-center\"><td colspan=\"5\">暂无数据</td></tr>";
            }

            while($dataQueryRow = mysqli_fetch_assoc($dataQuery)){
                $id = $dataQueryRow["id"];
                $name = $dataQueryRow["name"];
                $comments = $dataQueryRow["comments"];

                $cnt = db::num_rows("oj", "select username from user_info where userdefine = '{$id}'");

                echo "<tr class=\"text-center\">";
                echo "<td>#{$id}</td>";
                echo "<td>{$name}</td>";
                echo "<td>{$comments}</td>";
                echo "<td><a href=\"/user/list?gid={$id}\" target=\"_blank\">{$cnt}</a></td>";

                if ($id == 0) {
                    echo '<td><a href = "#" data-toggle="modal" data-target="#editModel" data-id="'.$id.'" data-groupname="'.$name.'" data-comments="'.$comments.'" "><span class="glyphicon glyphicon-pencil"></span>编辑</a> &nbsp; <span class="glyphicon glyphicon-remove">删除</td>';
                } else {
                    echo '<td><a href = "#" data-toggle="modal" data-target="#editModel" data-id="'.$id.'" data-groupname="'.$name.'" data-comments="'.$comments.'" "><span class="glyphicon glyphicon-pencil"></span>编辑</a> &nbsp; <a href="#"  data-toggle="modal" data-target="#confirmModel" data-id="'.$id.'" data-groupname="'.$name.'" ><span class="glyphicon glyphicon-remove">删除</a></td>';
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
                <a class="page-link" href="/user/group/page/1<?= $searchRedirect ?>" aria-label="Previous">
                    <span aria-hidden="true">&laquo;</span>
                </a>
            </li>

            <?php
                $cnt = 0;

                if($page - 2 > 0){
                    ++$cnt;
                    echo "<li class=\"page-item\"><a class=\"page-link\" href=\"/user/group/page/".($page-2).$searchRedirect."\">".($page-2)."</a></li>";
                }

                if($page - 1 > 0){
                    ++$cnt;
                    echo "<li class=\"page-item\"><a class=\"page-link\" href=\"/user/group/page/".($page-1).$searchRedirect."\">".($page-1)."</a></li>";
                }
            ?>

            <li class="page-item active"><a class="page-link" href="#"><?php echo $page; ?></a></li>
            
            <?php
                $id = 1;

                for($id; $id + $cnt < 5; ++$id){
                    if($id + $page <= $totalPage){
                        echo "<li class=\"page-item\"><a class=\"page-link\" href=\"/user/group/page/".($page+$id).$searchRedirect."\">".($page+$id)."</a></li>";
                    }
                }
            ?>
            
            <li class="page-item">
                <a class="page-link" href="/user/group/page/<?php echo $totalPage.$searchRedirect; ?>" aria-label="Next">
                <span aria-hidden="true">&raquo;</span>
                </a>
            </li>
        </ul>
    </nav>
    <script>
        $('#confirmModel').on('show.bs.modal', function (event) {
            var button = $(event.relatedTarget)
            var id = button.data('id')
            var groupname = button.data('groupname')

            var modal = $(this)
            modal.find('.modal-title').text('删除 ' + groupname + ' 用户组')

            modal.find('#input-confirm-id').val(id)
            modal.find('#input-confirm-groupname').val(groupname)
            modal.find('#input-confirm-action').val("delete")            
        });

        $('#editModel').on('show.bs.modal', function (event) {
            var button = $(event.relatedTarget)
            var id = button.data('id')
            var groupname = button.data('groupname')
            var comments = button.data('comments')

            var modal = $(this)
            modal.find('.modal-title').text('修改 ' + groupname + ' 用户组信息')

            modal.find('#div-id input').val(id)
            modal.find('#div-groupname input').val(groupname)
            modal.find('#div-comments textarea').val(comments)
        })

        $('#editModel').on('hidden.bs.modal', function (event) {
            showErrorHelp('groupname');
            showErrorHelp('comments');
        })

        $('#addModel').on('show.bs.modal', function (event) {
            var modal = $(this)
            modal.find('.modal-title').text('新增用户组')
        })

        $('#addModel').on('hidden.bs.modal', function (event) {
            showErrorHelp('groupname');
            showErrorHelp('comments');
        })
    </script>
<?= html::footer(); ?>