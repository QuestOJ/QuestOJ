<?php

    /**
	 * 子框架 : 渲染异常信息 (/framework/frame_report_HTMLPrint.php)
	 */

    if(!defined("framework_load")){
        header("Location:/403");
	}
	
	frame::HTTPCode(500);

	function stylePath($path) {
		$path = str_replace(PATH."\\", "", $path);
		$path = str_replace('\\', '/', $path);
		return $path;
	}
?>

<!DOCTYPE html>
<html lang="zh-cn">
	<head>
		<title>An Error Occured <?php if(!empty($siteName)) echo "- {$siteName}"; ?></title>
		<meta charset="utf-8" />

		<link href="/static/css/bootstrap.min.css" rel="stylesheet">
		<link href="/static/css/style.css" rel="stylesheet">

	</head>
	<body role="document">
		<div class="container theme-showcase" role="main">
			<div>
				<font color='red'><h1>System Error <?= $logCode ?></h1></font>
			</div>
			
			<div class="card mb-2">
				<div class="card-body"><?= stylePath($logText) ?></div>
			</div>
			
			<div>
				<h3>Debug Info</h3>
			</div>

			<div class="table-responsive">
				<table class="table table-bordered table-hover table-striped">
					<thead>
						<tr>
							<th class="text-center" style="width:6em;">ID</th>
							<th class="text-center" style="width:40%;">File</th>
							<th class="text-center" style="width:6em;">Line</th>
							<th class="text-center">Function</th>
						</tr>
					</thead>
					<tbody>
						<?php
							if(!$debug){
								echo "<tr class=\"text-center\"><td colspan=\"4\">Debug Info has been disabled.</td></tr>";
							}else{
								$stackCount = count($stackArray);

								//Finding Error Attemped
								$attemped = 0;

								for($transferID = 0; $transferID < $stackCount; ++$transferID){
									if($stackArray[$transferID]['function'] == "writeLog" || $stackArray[$transferID]['function'] == "dump"){
										$attemped = $transferID;
									}
								}
								
								//Print Transfer Stack
								for($transferID = $stackCount - 1; $transferID >= 0; --$transferID){
									if($transferID > $attemped)
										echo "<tr class='text-center table-success'>";
									else if($transferID == $attemped)
										echo "<tr class='text-center table-danger'>";
									else
										echo "<tr class='text-center table-warning'>";

									$ID = $stackCount - $transferID;
									echo "<td>#{$ID}</td>";
									
									//Absolute address trans to Relative address
									$fileName = stylePath($stackArray[$transferID]['file']);

									echo "<td>{$fileName}</td>";
									echo "<td>{$stackArray[$transferID]['line']}</td>";
									
									echo "<td>";
									if(!empty($stackArray[$transferID]['class'])){
										echo "{$stackArray[$transferID]['class']} >> ";
									}
									echo "{$stackArray[$transferID]['function']}()</td>";
									
									echo "</tr>";
								}

							}
                        ?>					
					</tbody>
				</table>
			</div>
			<div class="qoj-footer">
				<small>Framework <?= framework_version; ?>  /  System <?= VERSION; ?><?php if(!empty($siteUrl)) echo " / <a href='{$siteUrl}'>{$siteName}</a>"; ?></small>
			</div>
	</body>
</html>