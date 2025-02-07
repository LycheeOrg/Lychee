<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

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
	'duplicate-finder' => [
		'title' => 'Duplicates',
		'description' => 'This module counts potential duplicates betwen pictures.',
		'duplicates-all' => 'Duplicates over all albums',
		'duplicates-title' => 'Title duplicates per album',
		'duplicates-per-album' => 'Duplicates per album',
		'show' => 'Show duplicates',
	],
	'fix-jobs' => [
		'title' => 'Fixing Jobs History',
		'description' => 'Mark jobs with status <span class="text-ready-400">%s</span> or <span class="text-primary-500">%s</span> as <span class="text-danger-700">%s</span>.',
		'button' => 'Fix job history',
	],
	'gen-sizevariants' => [
		'title' => 'Missing %s',
		'description' => 'Found %d %s that could be generated.',
		'button' => 'Generate!',
		'success' => 'Successfully generated %d %s.',
	],
	'fill-filesize-sizevariants' => [
		'title' => 'File sizes missing',
		'description' => 'Found %d small variants without file size.',
		'button' => 'Fetch data!',
		'success' => 'Successfully computed sizes of %d small variants.',
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
	'flush-cache' => [
		'title' => 'Flush Cache',
		'description' => 'Flush the cache of every user to solve invalidation problems.',
		'button' => 'Flush',
	],
];