<?php

function getPasswordToStore($password, $username) {
	return md5($username . $password);
}
function checkPassword($user, $password) {
   	return $user['password'] == md5($user['username'] . $password);
}
function getPasswordClientSalt() {
	return UOJConfig::$data['security']['user']['client_salt'];
}

function crsf_token() {
	if (!isset($_SESSION['_token'])) {
		$_SESSION['_token'] = uojRandString(60);
	}
	return $_SESSION['_token'];
}
function crsf_check() {
	if (isset($_POST['_token'])) {
		$_token = $_POST['_token'];
	} else if (isset($_GET['_token'])) {
		$_token = $_GET['_token'];
	} else {
		return false;
	}
	return $_token === $_SESSION['_token'];
}
function crsf_defend() {
	if (!crsf_check()) {
		becomeMsgPage('This page has expired.');
	}
}

function captcha_check() {
	if (!UOJConfig::$data['security']['captcha']['available']) {
		return true;
	}
	if (!isset($_POST['recaptcha'])) {
		return false;
	}

	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, "https://www.recaptcha.net/recaptcha/api/siteverify");
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, array(
		"secret" => UOJConfig::$data['security']['captcha']['secret-key'],
		"response" => $_POST['recaptcha']
	));
    $response = curl_exec($ch);
	curl_close($ch);
	
	return json_decode($response, true)["success"];
}

function disable_for_anonymous() {
	global $myUser;
	if (UOJConfig::$data['security']['anonymous-visable'] == false && !Auth::check()) {
		redirectToLogin();
	}
}