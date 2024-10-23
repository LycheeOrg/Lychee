<?php

/**
 * Given a .env config constant, retrieve the env value and remove any trailing /.
 *
 * @param string      $cst     constant to fetch
 * @param string|null $default default value if does not exists
 *
 * @return string trimmed result
 */
if (!function_exists('renv')) {
	function renv(string $cst, ?string $default = null): string
	{
		return rtrim((string) (env($cst, $default) ?? ''), '/');
	}
}

/**
 * Allow to conditionally append an env value.
 *
 * @param string $cst constant to fetch
 *
 * @return string '' or env value prefixed with '/'
 */
if (!function_exists('renv_cond')) {
	function renv_cond(string $cst): string
	{
		return env($cst, '') === '' ? '' : ('/' . trim((string) env($cst), '/'));
	}
}

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
			'root' => env('LYCHEE_UPLOADS', public_path((string) env('LYCHEE_UPLOADS_DIR', 'uploads/'))),
			'url' => env('LYCHEE_UPLOADS_URL', '') !== '' ? renv('LYCHEE_UPLOADS_URL')
				: (renv('APP_URL', '') . renv_cond('APP_DIR') . '/' .
					renv('LYCHEE_UPLOADS_DIR', 'uploads')),
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

		's3' => [
			'driver' => 's3',
			'key' => env('AWS_ACCESS_KEY_ID', ''),
			'secret' => env('AWS_SECRET_ACCESS_KEY'),
			'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
			'bucket' => env('AWS_BUCKET'),
			'url' => env('AWS_URL'),
			'endpoint' => env('AWS_ENDPOINT'),
			'visibility' => env('AWS_IMAGE_VISIBILITY', 'public'),
			'use_path_style_endpoint' => env('AWS_USE_PATH_STYLE_ENDPOINT', false),
			'throw' => true,
		],

		// Lychee uses this disk to store the customized CSS file provided by the user
		// ATTENTION: This disk MUST ALWAYS point to the local `./public/dist` directory.
		// TODO: Maybe we should drop this Flysystem disk, because neither the driver nor the root must be changed and hence the whole point of using the Flysystem abstraction is gone.
		'dist' => [
			'driver' => 'local',
			'root' => env('LYCHEE_DIST', public_path('dist/')),
			'url' => env('LYCHEE_DIST_URL', renv_cond('APP_DIR') . '/dist/'),
			'visibility' => 'public',
		],

		// Lychee uses this disk to create ephemeral, symbolic links to photos,
		// if the feature is enabled.
		// For this feature to work, the "images" disk must use the "local" driver.
		// ATTENTION: This disk MUST ALWAYS use the "local" driver, because
		// Flysystem does not support symbolic links.
		'symbolic' => [
			'driver' => 'local',
			'root' => env('LYCHEE_SYM', public_path('sym')),
			'url' => env('LYCHEE_SYM_URL', '') !== '' ? renv('LYCHEE_SYM_URL') :
				(renv('APP_URL', 'http://localhost') . renv_cond('APP_DIR') . '/sym'),
			'visibility' => 'public',
		],

		// We use this space to temporarily store images when uploading.
		// Mostly chunks and incomplete images are placed here
		'image-upload' => [
			'driver' => 'local',
			'root' => env('LYCHEE_TMP_UPLOAD', storage_path('tmp/uploads')),
			'visibility' => 'private',
		],

		// We use this space to process the images,
		'image-jobs' => [
			'driver' => 'local',
			'root' => env('LYCHEE_IMAGE_JOBS', storage_path('tmp/jobs')),
			'visibility' => 'private',
		],

		// This is where we extract zip files before importing them.
		'extract-jobs' => [
			'driver' => 'local',
			'root' => env('LYCHEE_EXTRACT_JOBS', storage_path('tmp/extract')),
			'visibility' => 'private',
		],

		// For tests purposes
		'tmp-for-tests' => [
			'driver' => 'local',
			'root' => storage_path('tmp/uploads'),
			'visibility' => 'private',
		],
	],
];
