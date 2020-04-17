<?php

return [
	'update' => [
		// we need this in case the URL of the project changes
		'git' => 'http://api.github.com/repos/LycheeOrg/Lychee/commits',
		'json' => 'https://lycheeorg.github.io/update.json',
	],
	'git' => [
		'pull' => 'https://github.com/LycheeOrg/Lychee.git',
	],
];