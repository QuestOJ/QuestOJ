<?php

	if (!defined("load") || !isUserLogin()) {
		header("Location:/403");
		exit;
	}

	if (!auth::checkToken()) {
		exit("expired");
	}

	
	$blog_id = $_POST['blogid'];
	$contest_id = $_POST['id'];
	$action = $_POST["action"];
	$title = db::escape($_POST["title"]);

	if (!is_numeric($contest_id) || !is_numeric($blog_id)) {
		exit();
	}

	if ($action == "add" && empty($title)) {
		exit("title");
	}

	if (!$post = getPostInfo($blog_id)) {
		exit("id");
	}

	$str = DB::selectFirst("oj", "select * from contests where id='${contest_id}'");
	$all_config = json_decode($str['extra_config'], true);
	$config = $all_config['links'];

	$n = count($config);
	
	if ($action == 'add') {
		$row = array();
		$row[0] = $title;
		$row[1] = $blog_id;
		$config[$n] = $row;
		log::writelog(2, 3, 310, "添加比赛 #{$contest_id} 资料 {$title}");
	} else if ($action == 'delete') {
		$res = 0;
		for ($i = 0; $i < $n; $i++)
			if ($config[$i][1] == $blog_id) {
				log::writelog(2, 3, 311, "删除比赛 #{$contest_id} 资料 {$config[$i][0]}");
				$config[$i] = $config[$n - 1];
				unset($config[$n - 1]);
				$res = $i + 1;
				break;
			}

		if ($res == 0) {
			exit("id");
		}
	} else {
		exit();
	}

	$all_config['links'] = $config;
	$str = json_encode($all_config);
	$str = DB::escape($str);
	DB::query("oj", "update contests set extra_config='${str}' where id='${contest_id}'");

	exit("ok");
?>