<?php
	if (!UOJConfig::$data['security']['register']['available']) {
		become403Page();
	}

	function handleRegisterPost() {
		if (!crsf_check()) {
			return '页面已过期';
		}
		if (!captcha_check()) {
			return '验证码无效';
		}
		if (!isset($_POST['username'])) {
			return "无效表单";
		}
		if (!isset($_POST['password'])) {
			return "无效表单";
		}
		if (!isset($_POST['email'])) {
			return "无效表单";
		}
		if (!isset($_POST['realname'])) {
			return "无效表单";
		}
		$username = $_POST['username'];
		$password = $_POST['password'];
		$email = $_POST['email'];
		$esc_email = DB::escape($email);
		$realname = $_POST['realname'];
		$esc_realname = DB::escape($realname);

		if (!validateUsername($username)) {
			return "失败：无效用户名。";
		}
		if (queryUser($username)) {
			return "失败：用户名已存在。";
		}

		if (DB::num_rows("select username from user_info where email = '{$esc_email}'")) {
			return "失败：电子邮箱已存在。";
		}

		if (!validatePassword($password)) {
			return "失败：无效密码。";
		}
		if (!validateEmail($email)) {
			return "失败：无效电子邮箱。";
		}
		if (!validateRealname($realname)) {
			return "失败：无效姓名。";
		}

		$password = getPasswordToStore($password, $username);
		
		$svn_pw = uojRandString(10);
		
		if (!DB::selectCount("SELECT COUNT(*) FROM user_info"))
			DB::query("insert into user_info (username, email, password, svn_password, register_time, usergroup) values ('$username', '$esc_email', '$password', '$svn_pw', now(), 'S')");
		else
			DB::query("insert into user_info (username, email, password, svn_password, register_time) values ('$username', '$esc_email', '$password', '$svn_pw', now())");
		
		return "欢迎你！" . $username . "，你已成功注册。";
	}
	
	if (isset($_POST['register'])) {
		echo handleRegisterPost();
		die();
	} elseif (isset($_POST['check_username'])) {
		$username = $_POST['username'];
		if (validateUsername($username) && !queryUser($username)) {
			echo '{"ok" : true}';
		} else {
			echo '{"ok" : false}';
		}
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
<?php echoUOJPageHeader(UOJLocale::get('register')) ?>
<h2 class="page-header"><?= UOJLocale::get('register') ?></h2>
<form id="form-register" class="form-horizontal">
	<div id="div-email" class="form-group">
		<label for="input-email" class="col-sm-2 control-label"><?= UOJLocale::get('email') ?></label>
		<div class="col-sm-3">
			<input type="email" class="form-control" id="input-email" name="email" placeholder="<?= UOJLocale::get('enter your email') ?>" maxlength="50" />
			<span class="help-block" id="help-email"></span>
		</div>
	</div>
	<div id="div-username" class="form-group">
		<label for="input-username" class="col-sm-2 control-label"><?= UOJLocale::get('username') ?></label>
		<div class="col-sm-3">
			<input type="text" class="form-control" id="input-username" name="username" placeholder="<?= UOJLocale::get('enter your username') ?>" maxlength="20" />
			<span class="help-block" id="help-username"></span>
		</div>
	</div>
	<div id="div-password" class="form-group">
		<label for="input-password" class="col-sm-2 control-label"><?= UOJLocale::get('password') ?></label>
		<div class="col-sm-3">
			<input type="password" class="form-control" id="input-password" name="password" placeholder="<?= UOJLocale::get('enter your password') ?>" maxlength="20" />
			<input type="password" class="form-control top-buffer-sm" id="input-confirm_password" placeholder="<?= UOJLocale::get('re-enter your password') ?>" maxlength="20" />
			<span class="help-block" id="help-password"></span>
		</div>
	</div>
	<div id="div-realname" class="form-group">
		<label for="input-realname" class="col-sm-2 control-label"><?= UOJLocale::get('realname') ?></label>
		<div class="col-sm-3">
			<input type="text" class="form-control" id="input-realname" name="realname" placeholder="<?= UOJLocale::get('enter your realname') ?>" maxlength="20" />
			<span class="help-block" id="help-realname"></span>
		</div>
    </div>
	<?php if (UOJConfig::$data['security']['captcha']['available']): ?>
		<div id="div-recaptcha" class="form-group">
			<label for="input-recaptcha" class="col-sm-2 control-label"><?= UOJLocale::get('captcha') ?></label>
			<div class="g-recaptcha col-sm-3" data-sitekey="<?= UOJConfig::$data['security']['captcha']['site-key'] ?>"></div>
		</div>
	<?php endif ?>

	<div class="form-group">
		<div class="col-sm-offset-2 col-sm-3">
			<button type="submit" id="button-submit" class="btn btn-secondary"><?= UOJLocale::get('submit') ?></button>
		</div>
	</div>
</form>

<script type="text/javascript">
function checkUsernameNotInUse() {
	var ok = false;
	$.ajax({
		url : '/register',
		type : 'POST',
		dataType : 'json',
		async : false,
		
		data : {
			check_username : '',
			username : $('#input-username').val()
		},
		success : function(data) {
			ok = data.ok;
		},
		error :	function(XMLHttpRequest, textStatus, errorThrown) {
			alert(XMLHttpRequest.responseText);
			ok = false;
		}
	});
	return ok;
}
function validateRegisterPost() {
	var ok = true;
	ok &= getFormErrorAndShowHelp('email', validateEmail);
	ok &= getFormErrorAndShowHelp('realname', validateRealname);
	ok &= getFormErrorAndShowHelp('username', function(str) {
		var err = validateUsername(str);
		if (err)
			return err;
		if (!checkUsernameNotInUse())
			return '该用户名已被人使用了。';
		return '';
	})
	ok &= getFormErrorAndShowHelp('password', validateSettingPassword);
	return ok;
}

function submitRegisterPost() {
	if (!validateRegisterPost()) {
		return;
	}
	
	$.post('/register', {
		_token : "<?= crsf_token() ?>",
		register : '',
		username : $('#input-username').val(),
		email		: $('#input-email').val(),
		password : md5($('#input-password').val(), "<?= getPasswordClientSalt() ?>"), 
		realname : $('#input-realname').val(),
		<?php if (UOJConfig::$data['security']['captcha']['available']): ?>
		recaptcha : grecaptcha.getResponse()
		<?php endif ?>
	}, function(msg) {
		if (/^欢迎/.test(msg)) {
			BootstrapDialog.show({
				title	 : '注册成功',
				message : msg,
				type		: BootstrapDialog.TYPE_SUCCESS,
				buttons: [{
					label: '好的',
					action: function(dialog) {
						dialog.close();
					}
				}],
				onhidden : function(dialog) {
					var prevUrl = document.referrer;
					if (!prevUrl) {
						prevUrl = '/';
					};
					window.location.href = prevUrl;
				}
			});
		} else {
			BootstrapDialog.show({
				title	 : '注册失败',
				message : msg,
				type		: BootstrapDialog.TYPE_DANGER,
				buttons: [{
					label: '好的',
					action: function(dialog) {
						dialog.close();
						<?php if (UOJConfig::$data['security']['captcha']['available']): ?>
						grecaptcha.reset();
						<?php endif ?>
					}
				}],
			});
		}
	});
}
$(document).ready(function() {
	$('#form-register').submit(function(e) {
		submitRegisterPost();
		return false;
	});
});
</script>
<?php echoUOJPageFooter() ?>