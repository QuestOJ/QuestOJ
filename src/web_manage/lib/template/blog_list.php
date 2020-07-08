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

    $search = db::escape($_GET["s"]);

    $sql = "SELECT * FROM `blogs`";
    $searchRedirect = "";

    if (isset($_GET["s"])) {
        $sql = $sql." where (`title` like '%$search%' or `poster` like '%$search%')";
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
        header("Location:/blog/list/page/{$totalPage}".$searchRedirect);
        exit;
    }
    if($page < 1){
        header("Location:/blog/list/page/1".$searchRedirect);
        exit;
    }

    /**
     * 查询数据
     */
    
    $startID = ($page - 1) * $pageSize;
    $dataQuery = db::query("oj", $sql."ORDER BY post_time DESC LIMIT $startID, $pageSize"); 
?>

<?= html::header(); ?>
    <div class="modal fade" id="deleteModel" tabindex="-1" role="dialog" aria-labelledby="deleteModel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form id="form-delete" class="form-horizontal" method="post">
                <input type="hidden" id="input-delete-token" value="<?= frame::clientKey() ?>">
                <input type="hidden" id="input-delete-id" value="">

                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">删除</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div id="div-delete-title" class="form-group mb-1">
                        <label for="input-delete-title" class="col-form-label">博客标题:</label>
                        <input type="text" class="form-control" id="input-delete-title" disabled>
                        <span class="help-block" id="help-delete-title"></span>
                    </div>
                    <div id="div-delete-comments" class="form-group mb-1">
                        <label for="input-comments" class="col-form-label">备注:</label>
                        <textarea class="form-control" id="input-delete-comments" aria-label="With textarea" rows="3"></textarea>
                        <span class="help-block" id="help-delete-comments"></span>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary" id="button-submit">提交</button>
                </div>
                </form>
            </div>
            <script type="text/javascript">
                function validateDeletePost() {
                    var ok = true;
                    ok &= getFormErrorAndShowHelp('delete-comments', validateComments);
                    return ok;
                }

                function submitDeletePost() {
                    if (!validateDeletePost()) {
                        return false;
                    }
                    
                    $.post('/blog/edit', {
                        id : $('#input-delete-id').val(),
                        comments : $('#input-delete-comments').val(),
                        token : $('#input-delete-token').val(),
                        action : "delete",
                    }, function(msg) {
                        if (msg == 'ok') {
                            $('#deleteModel').modal('hide');
                            location.reload();
                        } else {
                            $('#div-delete-title').addClass('has-error');
                            $('#help-delete-title').html('未知错误。');
                        }
                    });

                    return true;
                }

                $(document).ready(function() {
                    $('#form-delete').submit(function(e) {
                        e.preventDefault();
                        submitDeletePost();
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
        </form>
        <script type="text/javascript">
        $('#form-search').submit(function(e) {
            e.preventDefault();
            
            url = '/blog/list';
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
                $id = $dataQueryRow["id"];
                $title = $dataQueryRow["title"];
                $poster = $dataQueryRow["poster"];
                $post_time = $dataQueryRow["post_time"];
                $is_hidden = $dataQueryRow["is_hidden"];

                echo "<tr class=\"text-center\">";
                echo "<td>#{$id}</td>";
                echo "<td><a href=\"".OJ_URL."/blogs/{$id}\" target=\"_blank\">{$title}</a></td>";
                echo "<td><a href=\"/user/list?s=".$poster."\" target=\"_blank\">{$poster}</a></td>";
                echo "<td>{$post_time}</td>";

                if ($is_hidden) {
                    echo "<td><b><font color='#DF013A'>已隐藏</font></b></td>";
                } else {
                    echo "<td><b><font color='#5FB404'>正常</font></b></td>";
                }

                    echo '<td><a href = "'.OJ_URL.'/blog/'.strtolower($poster).'/post/'.$id.'/write" target="_blank"><span class="glyphicon glyphicon-pencil"></span>编辑</a> &nbsp; <a href="#"  data-toggle="modal" data-target="#deleteModel" data-id="'.$id.'" data-title="'.$title.'" ><span class="glyphicon glyphicon-remove">删除</a></td>';
                

                echo "</tr>";
            }
        ?>
        </tbody>
    </table>
</div>

<nav aria-label="Page navigation">
        <ul class="pagination justify-content-center">
            <li class="page-item">
                <a class="page-link" href="/blog/list/page/1<?= $searchRedirect ?>" aria-label="Previous">
                    <span aria-hidden="true">&laquo;</span>
                </a>
            </li>

            <?php
                $cnt = 0;

                if($page - 2 > 0){
                    ++$cnt;
                    echo "<li class=\"page-item\"><a class=\"page-link\" href=\"/blog/list/page/".($page-2).$searchRedirect."\">".($page-2)."</a></li>";
                }

                if($page - 1 > 0){
                    ++$cnt;
                    echo "<li class=\"page-item\"><a class=\"page-link\" href=\"/blog/list/page/".($page-1).$searchRedirect."\">".($page-1)."</a></li>";
                }
            ?>

            <li class="page-item active"><a class="page-link" href="#"><?php echo $page; ?></a></li>
            
            <?php
                $id = 1;

                for($id; $id + $cnt < 5; ++$id){
                    if($id + $page <= $totalPage){
                        echo "<li class=\"page-item\"><a class=\"page-link\" href=\"/blog/list/page/".($page+$id).$searchRedirect."\">".($page+$id)."</a></li>";
                    }
                }
            ?>
            
            <li class="page-item">
                <a class="page-link" href="/blog/list/page/<?php echo $totalPage.$searchRedirect; ?>" aria-label="Next">
                <span aria-hidden="true">&raquo;</span>
                </a>
            </li>
        </ul>
    </nav>
    <script>
        $('#deleteModel').on('show.bs.modal', function (event) {
            var button = $(event.relatedTarget)
            var id = button.data('id')
            var title = button.data('title')

            var modal = $(this)
            modal.find('.modal-title').text('删除博客 ' + title)

            modal.find('#input-delete-id').val(id)
            modal.find('#input-delete-title').val(title)            
        });

        $('#deleteModel').on('hidden.bs.modal', function (event) {
            showErrorHelp('delete-title');
            showErrorHelp('delete-comments');
        })

    </script>
<?= html::footer(); ?>