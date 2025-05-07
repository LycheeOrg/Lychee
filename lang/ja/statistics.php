<?php

return [
	/*
	|--------------------------------------------------------------------------
	| Statistics page
	|--------------------------------------------------------------------------
	*/
	'title' => 'Statistics',

	'preview_text' => 'This is a preview of the statistics page available in Lychee <span class="text-primary-emphasis font-bold">SE</span>.<br />The data shown here are randomly generated and do not reflect your server.',
	'no_data' => 'User does not have data on server.',
	'collapse' => 'Collapse albums sizes',

	'total' => [
		'total' => 'Total',
		'albums' => 'Albums',
		'photos' => 'Photos',
		'size' => 'Size',
	],
	'table' => [
		'username' => 'Owner',
		'title' => 'Title',
		'photos' => 'Photos',
		'descendants' => 'Children',
		'size' => 'Size',
	],
	'punch_card' => [
		'title' => 'Activity',
		'photo-taken' => '%d photos taken',
		'photo-taken-in' => '%d photos taken in %d',
		'photo-uploaded' => '%d photos uploaded',
		'photo-uploaded-in' => '%d photos uploaded in %d',
		'with-exif' => 'with exif data',
		'less' => 'Less',
		'more' => 'More',
		'tooltip' => '%d photos on %s',
		'created_at' => 'Upload date',
		'taken_at' => 'Exif date',
		'caption' => 'Each column represents a week.',
	],
	'metrics' => [
		'header' => 'Live metrics',
		'a_visitor' => 'A visitor',
		'visitors' => '%d visitors',
		'visit_singular' => '%1$s viewed %2$s',
		'favourite_singular' => '%1$s favourited %2$s',
		'download_singular' => '%1$s downloaded %2$s',
		'shared_singular' => '%1$s shared %2$s',
		'visit_plural' => '%1$s viewed %2$s',
		'favourite_plural' => '%1$s favourited %2$s',
		'download_plural' => '%1$s downloaded %2$s',
		'shared_plural' => '%1$s shared %2$s',
		'ago' => [
			'days' => '%d days ago',
			'day' => 'a day ago',
			'hours' => '%d hours ago',
			'hour' => 'an hour ago',
			'minutes' => '%d minutes ago',
			'few_minutes' => 'a few minute ago',
			'seconds' => 'a few seconds ago',
		],
	],
];