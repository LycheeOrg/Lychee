<?php

return [
	'update' => [
		// we need this in case the URL of the project changes
		'git' => [
			'commits' => 'https://api.github.com/repos/LycheeOrg/Lychee/commits',
			'tags' => 'https://api.github.com/repos/LycheeOrg/Lychee/tags',
		],
		'json' => 'https://lycheeorg.github.io/update.json',
	],
	'git' => [
		'pull' => 'https://github.com/LycheeOrg/Lychee.git',
	],
];