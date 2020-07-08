<?php

    if(!defined("load") || !isUserLogin()){
        header("Location:/403");
        exit;
    }

    $chart_labels = "[";

    for ($i=-6; $i<=0; $i++) {
        $datetime=date("Y-m-d", strtotime($i. " day"));
        $sub_cnt = db::num_rows("oj", "SELECT `id` FROM `submissions` where to_days(submit_time) = to_days(\"$datetime\")"); 
        $custom_sub_cnt = db::num_rows("oj", "SELECT `id` FROM `custom_test_submissions` where to_days(submit_time) = to_days(\"$datetime\")"); 

        $chart_labels .= "\"".$datetime."\",";
        $chart_data .= $sub_cnt.",";
        $chart_custom_data .= $custom_sub_cnt.",";
    }

    $chart_labels .= "]";
?>
<?= html::header(); ?>

<ul class="nav nav-tabs" id="myTab" role="tablist">
  <li class="nav-item">
    <a class="nav-link active" id="submission-tab" data-toggle="tab" href="#submission" role="tab" aria-controls="submission" aria-selected="true">评测统计</a>
  </li>
  <li class="nav-item">
    <a class="nav-link" id="statistic-tab" data-toggle="tab" href="#statistic" role="tab" aria-controls="statistic" aria-selected="false">数据统计</a>
  </li>
  <li class="nav-item">
    <a class="nav-link" id="status-tab" data-toggle="tab" href="#status" role="tab" aria-controls="status" aria-selected="false">系统状态</a>
  </li>
</ul>
<div class="tab-content">
    <div class="tab-pane fade show active" id="submission" role="tabpanel" aria-labelledby="submission-tab">
        <div style="width:75%;display:block;margin:0 auto;"><div class="chartjs-size-monitor"><div class="chartjs-size-monitor-expand"><div class=""></div></div><div class="chartjs-size-monitor-shrink"><div class=""></div></div></div>
		    <canvas id="canvas" style="display: block; height: 446px; width: 893px;" width="1339" height="669" class="chartjs-render-monitor"></canvas>
	    </div>
    </div>
    <div class="tab-pane fade" id="statistic" role="tabpanel" aria-labelledby="statistic-tab">
    <div class="row row-centered">
        <div class="col-md-3 col-centered"></div>
        <div class="col-md-6 col-centered">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th class="text-center" style="width:40%;">项目</th>
                        <th class="text-center" style="width:60%;">值</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="text-center">题库总数</td>
                        <td class="text-center"><?= db::num_rows("oj", "SELECT `id` FROM `problems`") ?></td>
                    </tr>
                    <tr>
                        <td class="text-center">比赛总数</td>
                        <td class="text-center"><?= db::num_rows("oj", "SELECT `id` FROM `contests`") ?></td>
                    </tr>
                    <tr>
                        <td class="text-center">用户总数</td>
                        <td class="text-center"><?= db::num_rows("oj", "SELECT `username` FROM `user_info`") ?></td>
                    </tr>
                    <tr>
                        <td class="text-center">博客总数</td>
                        <td class="text-center"><?= db::num_rows("oj", "SELECT `id` FROM `blogs`") ?></td>
                    </tr>
                    <tr>
                        <td class="text-center">总提交数</td>
                        <td class="text-center"><?= db::num_rows("oj", "SELECT `id` FROM `submissions`") ?></td>
                    </tr>
                    <tr>
                        <td class="text-center">7天内提交数</td>
                        <td class="text-center"><?= db::num_rows("oj", "SELECT `id` FROM `submissions` where to_days(submit_time) >= to_days(\"".date("Y-m-d", strtotime("-6 day"))."\")"); ?></td>
                    </tr>
                    <tr>
                        <td class="text-center">30天内提交数</td>
                        <td class="text-center"><?= db::num_rows("oj", "SELECT `id` FROM `submissions` where to_days(submit_time) >= to_days(\"".date("Y-m-d", strtotime("-29 day"))."\")"); ?></td>
                    </tr>
                    <tr>
                        <td class="text-center">总自定义测试数</td>
                        <td class="text-center"><?= db::num_rows("oj", "SELECT `id` FROM `custom_test_submissions`") ?></td>
                    </tr>
            </table>
            </div>
        </div>
    </div>
    <div class="tab-pane fade" id="status" role="tabpanel" aria-labelledby="status-tab">
    <div class="row row-centered">
        <div class="col-md-3 col-centered"></div>
        <div class="col-md-6 col-centered">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th class="text-center" style="width:40%;">项目</th>
                        <th class="text-center" style="width:60%;">值</th>
                    </tr>
                </thead>
                <tbody>
                <tr>
                        <td class="text-center">服务器时间</td>
                        <td class="text-center"><?php echo date("Y-m-d H:i:s"); ?></td>
                    </tr>
                    <tr>
                        <td class="text-center">PHP 版本</td>
                        <td class="text-center"><?php echo PHP_VERSION; ?></td>
                    </tr>
                    <tr>
                        <td class="text-center">Zend 版本</td>
                        <td class="text-center"><?php echo zend_version(); ?></td>
                    </tr>
                    <tr>
                        <td class="text-center">服务器操作系统</td>
                        <td class="text-center"><?php echo PHP_OS; ?></td>
                    </tr>
                    <tr>
                        <td class="text-center">服务器最大上传限制</td>
                        <td class="text-center"><?PHP echo get_cfg_var("upload_max_filesize"); ?></td>
                    </tr>
                    <tr>
                        <td class="text-center">服务器允许最大执行时间</td>
                        <td class="text-center"><?php echo get_cfg_var("max_execution_time"); ?>秒</td>
                    </tr>
                    <tr>
                        <td class="text-center">服务器允许最大内存</td>
                        <td class="text-center"><?php echo get_cfg_var("memory_limit"); ?></td>
                    </tr>
            </table>
        </div>
    </div>
</div>

	<script>
		var config = {
			type: 'line',
			data: {
				labels: <?= $chart_labels ?>,
				datasets: [{
					label: '提交测试',
					data: [
						<?= $chart_data ?>
					],
					fill: false,
                    borderColor: "#6A5ACD",
                    backgroundColor: "#6A5ACD",
                    pointBackgroundColor: "#6A5ACD",
                    pointBorderColor: "#6A5ACD",
                    pointHoverBackgroundColor: "#6A5ACD",
                    pointHoverBorderColor: "#6A5ACD",
				},{
					label: '自定义测试',
					data: [
						<?= $chart_custom_data ?>
					],
					fill: false,
                    borderColor: "#ff8936",
                    backgroundColor: "#ff8936",
                    pointBackgroundColor: "#ff8936",
                    pointBorderColor: "#ff8936",
                    pointHoverBackgroundColor: "#ff8936",
                    pointHoverBorderColor: "#ff8936",                    
                }]
			},
			options: {
				responsive: true,
				title: {
					display: true,
					text: '一周评测统计'
				},
				tooltips: {
					mode: 'index',
					intersect: false,
				},
				hover: {
					mode: 'nearest',
					intersect: true
				},
				scales: {
					xAxes: [{
						display: true,
						scaleLabel: {
							display: false,
							labelString: '日期'
						}
					}],
					yAxes: [{
						display: true,
						scaleLabel: {
							display: false,
							labelString: '数量'
						}
					}]
				}
			}
		};

		window.onload = function() {
			var ctx = document.getElementById('canvas').getContext('2d');
			window.myLine = new Chart(ctx, config);
		};
	</script>
<?= html::footer(); ?>