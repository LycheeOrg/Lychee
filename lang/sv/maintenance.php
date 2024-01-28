<?php

return [
	/*
	|--------------------------------------------------------------------------
	| Update Page
	|--------------------------------------------------------------------------
	*/
	'title' => 'Maintenance',
	'description' => 'You will find on this page, all the required actions to keep your Lychee installation running smooth and nicely.',
	'cleaning' => [
		'title' => 'Cleaning %s',
		'result' => '%s deleted.',
		'description' => 'Remove all contents from <span class="font-mono">%s</span>',
		'button' => 'Clean',
	],
	'fix-jobs' => [
		'title' => 'Fixing Jobs History',
		'description' => 'Mark jobs with status <span class="text-ready-400">%s</span> or <span class="text-primary-500">%s</span> as <span class="text-danger-700">%s</span>.',
		'button' => 'Fix job history',
	],
	'fix-tree' => [
		'title' => 'Tree statistics',
		'Oddness' => 'Oddness',
		'Duplicates' => 'Duplicates',
		'Wrong parents' => 'Wrong parents',
		'Missing parents' => 'Missing parents',
		'button' => 'Fix tree',
	],
	'optimize' => [
		'title' => 'Optimize Database',
		'description' => 'If you notice slowdown in your installation, it may be because your database does not
		have all its needed index.',
		'button' => 'Optimize Database',
	],
	'update' => [
		'title' => 'Updates',
		'check-button' => 'Check for updates',
		'update-button' => 'Update',
		'no-pending-updates' => 'No pending update.',
	],
];