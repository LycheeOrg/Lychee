<?php

return [
	/*
	|--------------------------------------------------------------------------
	| Default Filesystem Disk
	|--------------------------------------------------------------------------
	|
	| Here you may specify the default filesystem disk that should be used
	| by the framework. The "local" disk, as well as a variety of cloud
	| based disks are available to your application. Just store away!
	|
	*/

	'default' => 'images',

	/*
	|--------------------------------------------------------------------------
	| Default Cloud Filesystem Disk
	|--------------------------------------------------------------------------
	|
	| Many applications store files both locally and in the cloud. For this
	| reason, you may specify a default "cloud" driver here. This driver
	| will be bound as the Cloud disk implementation in the container.
	|
	*/

	'cloud' => env('FILESYSTEM_CLOUD', 's3'),

	/*
	|--------------------------------------------------------------------------
	| Filesystem Disks
	|--------------------------------------------------------------------------
	|
	| Here you may configure as many filesystem "disks" as you wish, and you
	| may even configure multiple disks of the same driver. Defaults have
	| been setup for each driver as an example of the required options.
	|
	| Supported Drivers: "local", "ftp", "sftp", "s3"
	|
	*/

	'disks' => [
		// Lychee uses the disk "images" to store the media files
		'images' => [
			'driver' => 'local',
			'root' => env('LYCHEE_UPLOADS', public_path('uploads/')),
			'url' => env('LYCHEE_UPLOADS_URL', 'uploads/'),
			'visibility' => env('LYCHEE_IMAGE_VISIBILITY', 'public'),
			'directory_visibility' => env('LYCHEE_IMAGE_VISIBILITY', 'public'),
			'permissions' => [
				'file' => [
					'world' => 00666,
					'public' => 00664,
					'private' => 00660,
				],
				'dir' => [
					'world' => 02777,
					'public' => 02775,
					'private' => 02770,
				],
			],
		],

		// This is an example how the "images" disk can be hosted on an AWS S3
		// ATTENTION: This is NOT supported yet!!!
		// This is only a placeholder/reminder for the future
		/*
		'images' => [
			'driver' => 's3',
			'key' => env('AWS_ACCESS_KEY_ID'),
			'secret' => env('AWS_SECRET_ACCESS_KEY'),
			'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
			'bucket' => env('AWS_BUCKET'),
			'url' => env('AWS_URL'),
			'endpoint' => env('AWS_ENDPOINT'),
			'use_path_style_endpoint' => env('AWS_USE_PATH_STYLE_ENDPOINT', false),
			'throw' => true,
		],*/

		// Lychee uses this disk to create ephemeral, symbolic links to photos,
		// if the feature is enabled.
		// For this feature to work, the "images" disk must use the "local" driver.
		// ATTENTION: This disk MUST ALWAYS use the "local" driver, because
		// Flysystem does not support symbolic links.
		'symbolic' => [
			'driver' => 'local',
			'root' => public_path('sym'),
			'url' => 'sym',
			'visibility' => 'public',
		],
	],
];
