<?php

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

		'wp-login.php',
		'Portal/Portal.mwsl',
		'Portal0000.htm',
		'wp-cron.php',

		'aQQY',
		'nmaplowercheck1686252089',
		'sdk',
	],

	/**
	 * Because of all the combinations, it is more interesting to do a cross product.
	 */
	'xpaths' => [
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
			'asp',
			'aspx',
			'cgi',
			'html',
			'jhtml',
			'php',
			'pl',
			'shtml',
		],
	],
];