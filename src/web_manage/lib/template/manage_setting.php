<?php

    if(!defined("load") || !isUserLogin()){
        header("Location:/403");
        exit;
    }    
?>
<?= html::header(); ?>

<h2 class="page-header">系统设置</h2>
<form id="form-setting" class="form-horizontal" method="post">
  <div id="div-sitename" class="form-group">
    <label for="input-sitename" class="col-sm-2 control-label">站点名称</label>
    <div class="col-sm-3">
      <input type="text" class="form-control" id="input-sitename" name="sitename" value="<?= __siteName ?>"/>
      <span class="help-block" id="help-sitename"></span>
    </div>
  </div>
  <div id="div-siteurl" class="form-group">
    <label for="input-siteurl" class="col-sm-2 control-label">站点地址</label>
    <div class="col-sm-3">
      <input type="text" class="form-control" id="input-siteurl" name="siteurl" value="<?= __siteUrl ?>"/>
      <span class="help-block" id="help-siteurl"></span>
    </div>
  </div>
  <div id="div-siteshortname" class="form-group">
    <label for="input-siteshortname" class="col-sm-2 control-label">站点简称</label>
    <div class="col-sm-3">
      <input type="text" class="form-control" id="input-siteshortname" name="siteshortname" value="<?= __siteShortName ?>"/>
      <span class="help-block" id="help-siteshortname"></span>
    </div>
  </div>
  <div id="div-logintime" class="form-group">
    <label for="input-logintime" class="col-sm-2 control-label">登录有效期(秒)</label>
    <div class="col-sm-3">
      <input type="text" class="form-control" id="input-logintime" name="logintime" value="<?= __loginTime ?>"/>
      <span class="help-block" id="help-logintime"></span>
    </div>
  </div>
  <div class="form-group">
    <div class="col-sm-offset-2 col-sm-3">
      <button type="submit" id="button-submit" class="btn btn-secondary">提交</button>
    </div>
  </div>
</form>

<script type="text/javascript">
function validateSettingPost() {
	var ok = true;
	ok &= getFormErrorAndShowHelp('sitename', validateSiteName);
	ok &= getFormErrorAndShowHelp('siteurl', validateSiteURL);
  ok &= getFormErrorAndShowHelp('siteshortname', validateSiteShortName);
  ok &= getFormErrorAndShowHelp('logintime', validateLoginTime);
	return ok;
}

function submitSettingPost() {
	if (!validateSettingPost()) {
		return false;
	}
	
	$.post('/manage/setting/submit', {
		token : "<?= frame::clientKey() ?>",
		sitename : $('#input-sitename').val(),
    siteurl : $('#input-siteurl').val(),
    siteshortname : $('#input-siteshortname').val(),
    logintime : $('#input-logintime').val(),
	}, function(msg) {
		if (msg == 'ok') {
			location.reload();
		} else if (msg == 'expired') {
			$('#div-sitename').addClass('has-error');
			$('#help-sitename').html('页面会话已过期。');
		} else {
			$('#div-sitename').addClass('has-error');
			$('#help-sitename').html('未知错误。');
		}
	});
	return true;
}

$(document).ready(function() {
	$('#form-setting').submit(function(e) {
		e.preventDefault();
		submitSettingPost();
	});
});

</script>
<?= html::footer(); ?>