<?php
	if (Auth::check()) {
		redirectTo('/');
	}

	if (!Auth::checkLogin()) {
		become403Page();
	}

	function sendMail(){
		global $myUser;
		$verifyCode = uojRandString(8, '0123456789');
		$html = <<<EOD
<base target="_blank" />

<p>{$myUser['username']}您好，</p>
<p>您的验证码是：{$verifyCode}</p>
<p>{$oj_name}</p>

<style type="text/css">
body{font-size:14px;font-family:arial,verdana,sans-serif;line-height:1.666;padding:0;margin:0;overflow:auto;white-space:normal;word-wrap:break-word;min-height:100px}
pre {white-space:pre-wrap;white-space:-moz-pre-wrap;white-space:-pre-wrap;white-space:-o-pre-wrap;word-wrap:break-word}
</style>
EOD;

                $mailer = UOJMail::noreply();
                $mailer->addAddress($myUser['email'], $myUser['username']);
                $mailer->Subject = "邮箱验证";
                $mailer->msgHTML($html);
                if ($mailer->send()) {
                	DB::update("update user_info set code = '$verifyCode' where username = '{$myUser['username']}'");
		        return true;
                } else {
                        error_log('PHPMailer: '.$mailer->ErrorInfo);
                }

		return false;
	}

        if (isset($_POST['sendmail'])) {
		if (sendMail()) {
			echo 'good';
		}
		die();
        }

	function handleLoginPost() {
		global $myUser;

		if (!crsf_check()) {
			return 'expired';
		}
		if (!captcha_check()) {
			return 'recaptcha';
		}
		if (!isset($_POST['code'])) {
			return "failed";
		}
		$code = $_POST['code'];
		
		if (!validateCode($code)) {
			return "failed";
		}
	
		if ($code != DB::selectFirst("select code from user_info where username = '{$myUser['username']}'")['code']) {
			return "failed";
		}

        $esc_realname = DB::escape($realname);
		DB::update("update user_info set verify='1' where username = '{$myUser['username']}'");
		DB::update("update user_info set code = '' where username = '{$myUser['username']}'");
		$_SESSION["verify"] = 1;	
		return "ok";
	}
	
	if (isset($_POST['login'])) {
		echo handleLoginPost();
		die();
	}
?>
<?php
	$REQUIRE_LIB['md5'] = '';
	$REQUIRE_LIB['dialog'] = '';
	if (UOJConfig::$data['security']['captcha']['available']) {
		$REQUIRE_LIB['recaptcha'] = '';
	}
?>
<?php echoUOJPageHeader(UOJLocale::get('email auth')) ?>
<h2 class="page-header"><?= UOJLocale::get('email auth') ?></h2>
<form id="form-login" class="form-horizontal" method="post">
  <div id="div-code" class="form-group">
    <label for="input-code" class="col-sm-2 control-label"><?= UOJLocale::get('verification code') ?></label>
    <div class="col-sm-3">
      <input type="text" class="form-control" id="input-code" name="code" placeholder="<?= UOJLocale::get('enter your verification code') ?>" maxlength="20" />
      <span class="help-block" id="help-code"></span>
    </div>
  </div>
  <?php if (UOJConfig::$data['security']['captcha']['available']): ?>
	<div id="div-recaptcha" class="form-group">
		<label for="input-recaptcha" class="col-sm-2 control-label"><?= UOJLocale::get('captcha') ?></label>
		<div class="g-recaptcha col-sm-3" data-sitekey="<?= UOJConfig::$data['security']['captcha']['site-key'] ?>"></div>
		<div class="col-sm-3">
			<span class="help-block" id="help-recaptcha"></span>
		</div>
	</div>
  <?php endif ?>
  <div class="form-group">
    <div class="col-sm-offset-2 col-sm-3">
      <button type="button" id="button-sendmail" class="btn btn-secondary">获取验证码</button>&nbsp;&nbsp;
      <button type="submit" id="button-submit" class="btn btn-secondary"><?= UOJLocale::get('submit') ?></button>
    </div>
  </div>
</form>

<script type="text/javascript">
$('#button-sendmail').click(function(){
	$.post('/register/verify', {
		sendmail : ''
	}, function(res) {
		if (res == "good") {
			BootstrapDialog.show({
				title   : "操作成功",
				message : "验证码已经发送至您的邮箱，请查收。",
				type    : BootstrapDialog.TYPE_SUCCESS,
				buttons: [{
					label: '好的',
					action: function(dialog) {
						dialog.close();
					}
				}],
			});
		} else {
			BootstrapDialog.show({
				title   : "操作失败",
				message : "邮件未发送成功",
				type    : BootstrapDialog.TYPE_DANGER,
				buttons: [{
					label: '好吧',
					action: function(dialog) {
						dialog.close();
					}
				}],
			});
		}
	});
});

function validateLoginPost() {
	var ok = true;
	ok &= getFormErrorAndShowHelp('code', validateCode);
	return ok;
}

function submitLoginPost() {
	if (!validateLoginPost()) {
		return false;
	}
	
	$.post('/register/verify', {
		_token : "<?= crsf_token() ?>",
		login : '',
		code : $('#input-code').val(),
		<?php if (UOJConfig::$data['security']['captcha']['available']): ?>
		recaptcha : grecaptcha.getResponse()
		<?php endif ?>
	}, function(msg) {
		if (msg == 'ok') {
			var prevUrl = document.referrer;
			if (prevUrl == '' || /.*\/login.*/.test(prevUrl) || /.*\/logout.*/.test(prevUrl) || /.*\/register.*/.test(prevUrl) || /.*\/reset-password.*/.test(prevUrl)) {
				prevUrl = '/';
			};
			window.location.href = prevUrl;
		} else if (msg == 'expired') {
			$('#div-code').addClass('has-error');
			$('#help-code').html('页面会话已过期。');
			<?php if (UOJConfig::$data['security']['captcha']['available']): ?>
			grecaptcha.reset();
			<?php endif ?>
		} else if (msg == 'recaptcha') {
			$('#div-recaptcha').addClass('has-error');
			$('#help-recaptcha').html('人机验证未通过。');
			<?php if (UOJConfig::$data['security']['captcha']['available']): ?>
			grecaptcha.reset();
			<?php endif ?>			
		} else {
			$('#div-code').addClass('has-error');
			$('#help-code').html('验证码错误。');
			<?php if (UOJConfig::$data['security']['captcha']['available']): ?>
			grecaptcha.reset();
			<?php endif ?>
		}
	});
	return true;
}

$(document).ready(function() {
	$('#form-login').submit(function(e) {
		e.preventDefault();
		submitLoginPost();
	});
});

</script>
<?php echoUOJPageFooter() ?>
