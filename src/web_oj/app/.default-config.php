<?php
return [
	'manage_platform' => 'http://127.0.0.2',
	'profile' => [
		'oj-name'  => 'Quest Online Judge',
		'oj-name-short' => 'QOJ',
		'administrator' => 'root',
		'admin-email' => 'postmaster@questoj.cn',
		'QQ-group' => '',
		'ICP-license' => '',
		'docs-url' => '/faq'
	],
	'database' => [
		'database'  => 'app_uoj233',
		'username' => 'root',
		'password' => '',
		'host' => '127.0.0.1'
	],
	'web' => [
		'domain' => null,
		'main' => [
			'protocol' => 'http',
			'host' => '_httpHost_',
			'port' => 80
		],
		'blog' => [
			'protocol' => 'http',
			'host' => '_httpHost_',
			'port' => 80
		]
	],
	'security' => [
		'user' => [
			'client_salt' => 'salt0'
		],
		'cookie' => [
			'checksum_salt' => ['salt1', 'salt2', 'salt3']
		],
		'captcha' => [
			'available' => false,
			'site-key' => '',
			'secret-token' => ''
		],
		'register' => [
			'available' => true,
			'verify' => 0
		],
		'anonymous-visable' => true
	],
	'mail' => [
		'noreply' => [
			'username' => 'noreply@none',
			'password' => 'noreply',
			'host' => 'smtp.sina.com',
			'secure' => 'tls',
			'port' => 587
		]
	],
	'judger' => [
		'socket' => [
			'port' => '233',
			'password' => 'password233'
		]
	],
	'switch' => [
		'web-analytics' => false,
		'disable-hack' => false,
		'blog-domain-mode' => 3
	]
];
