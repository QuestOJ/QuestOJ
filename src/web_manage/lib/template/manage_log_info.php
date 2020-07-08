<?php

    if(!defined("load") || !isUserLogin()){
        header("Location:/403");
        exit;
    }
    
    /**
     * 解析日志 ID
     */
    $pregResult = preg_match_all("/\d+/", $_SERVER['REQUEST_URI'], $IDArray);

    if(!$pregResult){
        $logID = 1;
    }else{
        $logID = $IDArray[0][0];
    }

    /**
     * 查询日志信息
     */

    $dataQuery = db::query("local", "SELECT * FROM `".MYSQL_TABLE_PREFIX."_logs` where `ID` = '$logID' LIMIT 1"); 

    /**
     * 日志不存在
     */
    
    if(!mysqli_num_rows($dataQuery)){
        header("Location:/404");
        exit;
    }

    while($dataQueryRow = mysqli_fetch_assoc($dataQuery)){
        $logHash = $dataQueryRow['logHash'];
        $logText = frame::twoWayDecryption($dataQueryRow['logText'], AUTH_KEY, LOG_SALT);
        $logCode = $dataQueryRow['logCode'];
        $logUrl = frame::twoWayDecryption($dataQueryRow['logUrl'], AUTH_KEY, LOG_SALT);
        $logInfo = frame::twoWayDecryption($dataQueryRow['logInfo'], AUTH_KEY, LOG_SALT);
        $execStack = frame::twoWayDecryption($dataQueryRow['execStack'], AUTH_KEY, LOG_SALT);

        $clientKey = frame::twoWayDecryption($dataQueryRow['clientKey'], AUTH_KEY, LOG_SALT);
        $fieldGet = frame::twoWayDecryption($dataQueryRow['fieldGet'], AUTH_KEY, LOG_SALT);
        $fieldPost = frame::twoWayDecryption($dataQueryRow['fieldPost'], AUTH_KEY, LOG_SALT);
        $fieldCookie = frame::twoWayDecryption($dataQueryRow['fieldCookie'], AUTH_KEY, LOG_SALT);
        $fieldSession = frame::twoWayDecryption($dataQueryRow['fieldSession'], AUTH_KEY, LOG_SALT);

        $guestIP = $dataQueryRow['guestIP'];
        $guestOS = $dataQueryRow['guestOS'];
        $guestBrowser = $dataQueryRow['guestBrowser'];
        $userID = $dataQueryRow['userID'];
        $date = $dataQueryRow['date'];
    }
?>
<?= html::header(); ?>
            <div>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="#">首页</a></li>
                        <li class="breadcrumb-item"><a href="javascript:history.go(-1);">系统日志</a></li>
                        <li class="breadcrumb-item active" aria-current="page">日志详情</li>
                    </ol>
                </nav>
            </div>

			<div>
				<h3>事件 - <?php echo $logCode; ?></h3>
			</div>

			<div class="card mb-2">
				<div class="card-body">
					<?php echo $logText; ?>
				</div>
			</div>

			<div>
				<h3>访客信息</h3>
			</div>

			<div class="table-responsive">
				<table class="table table-bordered table-hover table-striped">
					<thead>
						<tr>
                        <th class="text-center" style="width:10%">日期</th>  
                            <th class="text-center" style="width:5em;">UID</th>                       
							<th class="text-center" style="width:10%;">IP</th>
							<th class="text-center" style="width:25%;">URL</th>
							<th class="text-center" style="width:10%;">操作系统</th>
                            <th class="text-center">浏览器</th>
						</tr>
					</thead>
					<tbody>
                        <tr class="text-center">
                            <td style="vertical-align: middle;"><?php echo $date; ?></td>
                            <td style="vertical-align: middle;">
                                <?php
                                    if(!$userID)
                                        echo "未登录";
                                    else
                                        echo $userID;
                                ?>
                            </td>
                            <td style="vertical-align: middle;"><a href="#" data-toggle="tooltip" data-placement="top" title="<?php echo frame::getIPLocation($guestIP); ?>"><?php echo $guestIP; ?></a></td>
                            <td style="vertical-align: middle;"><?php echo $logUrl; ?></td>
                            <td style="vertical-align: middle;"><?php echo $guestOS; ?></td>
                            <td style="vertical-align: middle;"><?php echo $guestBrowser; ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>

			<div>
				<h3>事件快照</h3>
			</div>          

			<div class="card mb-2">
				<div class="card-body">
                    <?php if(!empty($logInfo)) echo $logInfo; else echo "无快照"; ?><br/>
				</div>
			</div>

			<div>
				<h3>追踪日志</h3>
			</div>   

			<div class="card mb-2">
				<div class="card-body">
                    Log ID / <?php echo $logHash; ?><br/>
			        <?php echo $execStack; ?>
				</div>
			</div>

			<div>
				<h3>请求参数</h3>
			</div>   

			<div class="card mb-2">
				<div class="card-body">
                    <?php echo $clientKey; ?><br/>
                    <?php echo $fieldGet; ?><br/>
                    <?php echo $fieldPost; ?><br/>
                    <?php echo $fieldCookie; ?><br/>
                    <?php echo $fieldSession; ?><br/>
				</div>
			</div>

            <script>$(function(){$("[data-toggle='tooltip']").tooltip();});</script>
<?= html::footer(); ?>
