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

    $sql = "SELECT * FROM `contests`";
    $searchRedirect = "";

    if (isset($_GET["s"])) {
        $sql = $sql." where (`name` like '%$search%')";
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
        header("Location:/blog/contests/page/{$totalPage}".$searchRedirect);
        exit;
    }
    if($page < 1){
        header("Location:/blog/contests/page/1".$searchRedirect);
        exit;
    }

    /**
     * 查询数据
     */
    
    $startID = ($page - 1) * $pageSize;
    $dataQuery = db::query("oj", $sql."ORDER BY id DESC LIMIT $startID, $pageSize"); 
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
                    <div class="table-responsive mb-3">
                        <table id="table-blog-list">
                        </table>
                    </div>
                    <div id="div-action" class="from-group mb-1">
                        <label for="input-action" class="col-form-label">操作类型:</label>
                        <select class="custom-select" id="input-action">
                            <option value="add">添加</option>
                            <option value="delete">删除</option>
                        </select>
                    </div>
                    <div id="div-blogid" class="form-group mb-1">
                        <label for="input-blogid" class="col-form-label">博客ID:</label>
                        <input type="text" class="form-control" id="input-blogid">
                        <span class="help-block" id="help-blogid"></span>
                    </div>
                    <div id="div-title" class="form-group mb-1">
                        <label for="input-title" class="col-form-label">标题:</label>
                        <input type="text" class="form-control" id="input-title">
                        <span class="help-block" id="help-title"></span>
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
                    ok &= getFormErrorAndShowHelp('blogid', validateBlogID);
                    return ok;
                }
                function submitEditPost() {
                    if (!validateEditPost()) {
                        return false;
                    }

                    $.post('/blog/contests/edit', {
                        blogid : $('#input-blogid').val(),
                        id : $('#input-id').val(),
                        token : $('#input-token').val(),
                        action : $('#input-action').val(),
                        title : $('#input-title').val(),
                    }, function(msg) {
                        if (msg == 'ok') {
                            $('#editModel').modal('hide');
                            location.reload();
                        } else if (msg == 'expired') {
                            $('#div-blogid').addClass('has-error');
                            $('#help-blogid').html('页面会话已过期。');
                        } else if (msg == 'id') {
                            $('#div-blogid').addClass('has-error');
                            $('#help-blogid').html('博客ID不存在。');
                        } else if (msg == 'title') {
                            $('#div-title').addClass('has-error');
                            $('#help-title').html('标题不能为空。');
                        } else {
                            $('#div-blogid').addClass('has-error');
                            $('#help-blogid').html('未知错误.');
                        }
                    });

                    return true;
                }

                $(document).ready(function() {
                    $('#input-action').change(function(){
                        var selected=$(this).children('option:selected').val()


                        if (selected == "add") {
                            $('#div-title').removeClass("d-none")
                            showErrorHelp('title');
                            showErrorHelp('blogid');
                        } else if (selected == "delete") {
                            $('#div-title').addClass("d-none")
                            showErrorHelp('blogid');
                        }
                    });

                    $('#form-edit').submit(function(e) {
                        e.preventDefault();
                        submitEditPost();
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
            
            url = '/blog/contests';
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
                <th class="text-center">比赛名称</th>
                <th class="text-center">开始时间</th>
                <th class="text-center">时长</th>
                <th class="text-center">状态</th>
                <th class="text-center">操作</th>
            </tr>
        </thead>
        <tbody>
        <?php
            if ($total == 0) {
                echo "<tr class=\"text-center\"><td colspan=\"7\">暂无数据</td></tr>";
            }

            while($dataQueryRow = mysqli_fetch_assoc($dataQuery)){
                $id = $dataQueryRow["id"];
                $name = $dataQueryRow["name"];  
                $start_time = $dataQueryRow["start_time"];
                $last_hour = floatval(number_format($dataQueryRow["last_min"] / 60, 2));
                $status = $dataQueryRow["status"];

                echo "<tr class=\"text-center\">";
                echo "<td>#{$id}</td>";
                echo "<td><a href=\"".OJ_URL."/contest/{$id}\" target=\"_blank\">{$name}</a></td>";
                echo "<td>{$start_time}</td>";
                echo "<td>{$last_hour} 小时</td>";

                if ($status == "finished") {
                    echo "<td>已结束</td>";
                } else {
                    echo "<td>未结束</td>";
                }

                $links = json_decode($dataQueryRow["extra_config"], true)["links"];

                $n = count($links);
                $links_arr = array();

                for ($i = 0; $i < $n; $i++) {
                    $links_row_arr = array("id" => $links[$i][1], "title" => $links[$i][0]);
                    array_push($links_arr, $links_row_arr);
                }

                echo '<td><a href = "#" data-toggle="modal" data-target="#editModel" data-id="'.$id.'" data-links=\''.base64_encode(json_encode($links_arr)).'\'><span class="glyphicon glyphicon-pencil"></span>编辑</a></td>';

                echo "</tr>";
            }
        ?>
        </tbody>
    </table>
</div>

<nav aria-label="Page navigation">
        <ul class="pagination justify-content-center">
            <li class="page-item">
                <a class="page-link" href="/blog/contests/page/1<?= $searchRedirect ?>" aria-label="Previous">
                    <span aria-hidden="true">&laquo;</span>
                </a>
            </li>

            <?php
                $cnt = 0;

                if($page - 2 > 0){
                    ++$cnt;
                    echo "<li class=\"page-item\"><a class=\"page-link\" href=\"/blog/contests/page/".($page-2).$searchRedirect."\">".($page-2)."</a></li>";
                }

                if($page - 1 > 0){
                    ++$cnt;
                    echo "<li class=\"page-item\"><a class=\"page-link\" href=\"/blog/contests/page/".($page-1).$searchRedirect."\">".($page-1)."</a></li>";
                }
            ?>

            <li class="page-item active"><a class="page-link" href="#"><?php echo $page; ?></a></li>
            
            <?php
                $id = 1;

                for($id; $id + $cnt < 5; ++$id){
                    if($id + $page <= $totalPage){
                        echo "<li class=\"page-item\"><a class=\"page-link\" href=\"/blog/contests/page/".($page+$id).$searchRedirect."\">".($page+$id)."</a></li>";
                    }
                }
            ?>
            
            <li class="page-item">
                <a class="page-link" href="/blog/contests/page/<?php echo $totalPage.$searchRedirect; ?>" aria-label="Next">
                <span aria-hidden="true">&raquo;</span>
                </a>
            </li>
        </ul>
    </nav>
    <script>
        $('#editModel').on('show.bs.modal', function (event) {
            var button = $(event.relatedTarget)

            var id = button.data('id')
            var links = atob(button.data('links'))

            var modal = $(this)
            modal.find('.modal-title').text('修改比赛资料')

            var columns = [{ 
                field:"id",  
                title: 'ID'
            }, {
                field: 'title',
                title: '标题'
            }];
            
            var data = JSON.parse(links)
            
            modal.find('#input-id').val(id)
            $('#table-blog-list').bootstrapTable({  
                data:data,
                columns: columns,  
            });
        })

        $('#editModel').on('hidden.bs.modal', function (event) {
            showErrorHelp('title');
            showErrorHelp('blogid');
            $('#table-blog-list').bootstrapTable('destroy');
        })
    </script>
<?= html::footer(); ?>