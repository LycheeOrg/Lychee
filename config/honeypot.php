<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

return [
	/**
	 * Enable HoneyPot to return 418 when hitting honey.
	 */
	'enabled' => true,

	/**
	 * Honey.
	 *
	 * Set of possible path.
	 * Those will be concatenated into a regex.
	 */
	'paths' => [
		'.env',
		'.git/config',
		'.git/HEAD',
		'.well-known/security.txt',
		'.well-known/traffic-advice',

		'readme.txt',
		'pools',
		'pools/default/buckets',
		'__Additional',

		'CSS/Miniweb.css',
		'wp-login.php',
		'wp-content/plugins/core-plugin/include.php',
		'wp-content/plugins/woocommerce/readme.txt',
		'Portal/Portal.mwsl',
		'Portal0000.htm',

		'ads.txt',
		'aQQY',
		'UEPs',
		'HNAP1',
		'nmaplowercheck1686252089',
		'sdk',

		'backup',
		'bc',
		'bk',
		'blog',
		'home',
		'main',
		'new',
		'newsite',
		'old',
		'test',
		'testing',
		'wordpress',
		'wp-admin/install.php',
		'wp-admin/setup-config.php',
		'wp',
		'xmlrpc.php',

		'.vscode/sftp.json',
		'aws.json',
		'awsconfig.json',
		'AwsConfig.json',
		'client_secrets.json',
		'conf.json',
		'config/config.json',
		'credentials/config.json',
		'database-config.json',
		'db.json',
		'env.json',
		'smtp.json',
		'ssh-config.json',
		'user-config.json',
	],

	/**
	 * Because of all the combinations, it is more interesting to do a cross product.
	 */
	'xpaths' => [
		[ // admin, main default etc.
			'prefix' => [
				'admin',
				'base',
				'default',
				'home',
				'indice',
				'inicio',
				'localstart',
				'main',
				'menu',
				'start',
			],
			'suffix' => [
				'.asp',
				'.aspx',
				'.cgi',
				'.cfm',
				'.html',
				'.jhtml',
				'.inc',
				'.jsa',
				'.jsp',
				'.php',
				'.pl',
				'.shtml',
			],
		],
		[ // phpinfo sets
			'prefix' => [
				'',
				'_',
				'__',
				'html/',
			],
			'suffix' => [
				'info.php',
				'phpinfo.php',
			],
		],
	],
];