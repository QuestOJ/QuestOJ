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

    $pageSize = 15;

    /**
     * 获取搜索信息
     */

    $searchRedirect = "";
    $sql = "SELECT * FROM `important_blogs`";

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
        header("Location:/blog/announcement/page/{$totalPage}".$searchRedirect);
        exit;
    }
    if($page < 1){
        header("Location:/blog/announcement/page/1".$searchRedirect);
        exit;
    }

    /**
     * 查询数据
     */
    
    $startID = ($page - 1) * $pageSize;
    $dataQuery = db::query("oj", $sql."ORDER BY level DESC LIMIT $startID, $pageSize"); 
?>

<?= html::header(); ?>
<div class="modal fade" id="editModel" tabindex="-1" role="dialog" aria-labelledby="editModal" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form id="form-edit" class="form-horizontal" method="post">
                <input type="hidden" id="input-token" value="<?= frame::clientKey() ?>">
                <input type="hidden" id="input-id" value="">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">编辑</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div id="div-level" class="from-group mb-1">
                        <label for="input-level" class="col-form-label">公告等级:</label>
                        <select class="custom-select" id="input-level">
                            <option id="option-3" value="3">一级置顶</option>
                            <option id="option-2" value="2">二级置顶</option>
                            <option id="option-1" value="1">三级置顶</option>
                            <option id="option-0" value="0">普通</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary" id="button-submit">提交</button>
                </div>
                </form>
            </div>
            <script type="text/javascript">
                function submitEditPost() {
                    $.post('/blog/announcement/edit', {
                        level : $('#input-level').val(),
                        id : $('#input-id').val(),
                        token : $('#input-token').val(),
                        action : "edit",
                    }, function(msg) {
                        if (msg == 'ok') {
                            $('#editModel').modal('hide');
                            location.reload();
                        } else {
                            $('#div-level').addClass('has-error');
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
                    <h5 class="modal-title" id="exampleModalLabel">新增公告</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div id="div-add-id" class="form-group mb-1">
                        <label for="input-add-id" class="col-form-label">博客ID:</label>
                        <input type="text" class="form-control" id="input-add-id">
                        <span class="help-block" id="help-add-id"></span>
                    </div>
                    <div id="div-add-level" class="from-group mb-1">
                        <label for="input-add-level" class="col-form-label">公告等级:</label>
                        <select class="custom-select" id="input-add-level">
                            <option id="option-3" value="3">一级置顶</option>
                            <option id="option-2" value="2">二级置顶</option>
                            <option id="option-1" value="1">三级置顶</option>
                            <option id="option-0" value="0">普通</option>
                        </select>
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
                    ok &= getFormErrorAndShowHelp('add-id', validateBlogID);
                    return ok;
                }

                function submitAddPost() {
                    if (!validateAddPost()) {
                        return false;
                    }
                    
                    $.post('/blog/announcement/edit', {
                        level : $('#input-add-level').val(),
                        id : $('#input-add-id').val(),
                        token : $('#input-add-token').val(),
                        action : "add",
                    }, function(msg) {
                        if (msg == 'ok') {
                            $('#addModel').modal('hide');
                            location.reload();
                        } else if (msg == 'id') {
                            $('#div-add-id').addClass('has-error');
                            $('#help-add-id').html('博客ID不存在。');
                        } else if (msg == 'expired') {
                            $('#div-add-id').addClass('has-error');
                            $('#help-add-id').html('页面会话已过期。');
                        } else {
                            $('#div-add-id').addClass('has-error');
                            $('#help-add-id').html('未知错误。');
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
                    $.post('/blog/announcement/edit', {
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
        <button type="button" class="btn btn-default btn-primary" data-toggle="modal" data-target="#addModel" >新增公告</button>
    </nav>
</div>
<div class="table-responsive">
    <table class="table table-bordered table-hover table">
        <thead>
            <tr>
                <th class="text-center">ID</th>
                <th class="text-center">标题</th>
                <th class="text-center">作者</th>
                <th class="text-center">发布时间</th>
                <th class="text-center">状态</th>
                <th class="text-center">操作</th>
            </tr>
        </thead>
        <tbody>
        <?php
            if ($total == 0) {
                echo "<tr class=\"text-center\"><td colspan=\"6\">暂无数据</td></tr>";
            }

            while($dataQueryRow = mysqli_fetch_assoc($dataQuery)){
                $id = $dataQueryRow["blog_id"];
                $level = $dataQueryRow["level"];
                $blogs = getPostInfo($id);

                $title = $blogs["title"];
                $poster = $blogs["poster"];
                $post_time = $blogs["post_time"];

                echo "<tr class=\"text-center\">";
                echo "<td>#{$id}</td>";
                echo "<td><a href=\"".OJ_URL."/blogs/{$id}\" target=\"_blank\">{$title}</a></td>";
                echo "<td><a href=\"/user/list?s=".$poster."\" target=\"_blank\">{$poster}</a></td>";
                echo "<td>{$post_time}</td>";

                if ($level == 3) {
                    echo "<td><b><font color='#DF013A'>一级置顶</font></b></td>";
                }else if ($level == 2) {
                    echo "<td><b><font color='#DF013A'>二级置顶</font></b></td>";
                }else if ($level == 1) {
                    echo "<td><b><font color='#DF013A'>三级置顶</font></b></td>";
                } else {
                    echo "<td>普通</td>";
                }

                echo '<td><a href = "#" data-toggle="modal" data-target="#editModel" data-id="'.$id.'" data-level="'.$level.'"><span class="glyphicon glyphicon-pencil"></span>编辑</a> &nbsp; <a href="#"  data-toggle="modal" data-target="#confirmModel" data-id="'.$id.'" data-title="'.$title.'"><span class="glyphicon glyphicon-remove">删除</a></td>';

                echo "</tr>";
            }
        ?>
        </tbody>
    </table>
</div>

<nav aria-label="Page navigation">
        <ul class="pagination justify-content-center">
            <li class="page-item">
                <a class="page-link" href="/blog/announcement/page/1<?= $searchRedirect ?>" aria-label="Previous">
                    <span aria-hidden="true">&laquo;</span>
                </a>
            </li>

            <?php
                $cnt = 0;

                if($page - 2 > 0){
                    ++$cnt;
                    echo "<li class=\"page-item\"><a class=\"page-link\" href=\"/blog/announcement/page/".($page-2).$searchRedirect."\">".($page-2)."</a></li>";
                }

                if($page - 1 > 0){
                    ++$cnt;
                    echo "<li class=\"page-item\"><a class=\"page-link\" href=\"/blog/announcement/page/".($page-1).$searchRedirect."\">".($page-1)."</a></li>";
                }
            ?>

            <li class="page-item active"><a class="page-link" href="#"><?php echo $page; ?></a></li>
            
            <?php
                $id = 1;

                for($id; $id + $cnt < 5; ++$id){
                    if($id + $page <= $totalPage){
                        echo "<li class=\"page-item\"><a class=\"page-link\" href=\"/blog/announcement/page/".($page+$id).$searchRedirect."\">".($page+$id)."</a></li>";
                    }
                }
            ?>
            
            <li class="page-item">
                <a class="page-link" href="/blog/announcement/page/<?php echo $totalPage.$searchRedirect; ?>" aria-label="Next">
                <span aria-hidden="true">&raquo;</span>
                </a>
            </li>
        </ul>
    </nav>
    <script>
        $('#confirmModel').on('show.bs.modal', function (event) {
            var button = $(event.relatedTarget)
            var id = button.data('id')
            var title = button.data('title')

            var modal = $(this)
            modal.find('.modal-title').text('删除公告 ' + title)

            modal.find('#input-confirm-id').val(id)
            modal.find('#input-confirm-action').val("delete")            
        });

        $('#editModel').on('show.bs.modal', function (event) {
            var button = $(event.relatedTarget)

            var id = button.data('id')
            var level = button.data('level')

            var modal = $(this)
            modal.find('.modal-title').text('修改置顶等级')

            modal.find('#input-id').val(id)
            document.getElementById("option-"+level).selected=true;
        })

        $('#editModel').on('hidden.bs.modal', function (event) {
            showErrorHelp('level');
        })

        $('#addModel').on('hidden.bs.modal', function (event) {
            showErrorHelp('add-id');
        })
    </script>
<?= html::footer(); ?>