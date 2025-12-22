<?php

return [
	'enabled' => env('XHPROF_ENABLED', false),

	'skip' => [
		'/__clockwork/',
		'/_debugbar/',
	],
];
