<?php
	if (!validateUInt($_GET['id']) || !($blog = queryBlog($_GET['id']))) {
		become404Page();
	}

	disable_for_anonymous();
	
	redirectTo(HTML::blog_url($blog['poster'], '/post/'.$_GET['id']));
?>
