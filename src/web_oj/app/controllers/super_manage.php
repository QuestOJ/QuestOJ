<?php
	if (!Auth::check() || !isSuperUser(Auth::user())) {
		become403Page();
	}
	
	header("Location: ".UOJConfig::$data['manage_platform']."/login");
	exit();
?>