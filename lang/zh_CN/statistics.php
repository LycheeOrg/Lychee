<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

return [
	/*
	|--------------------------------------------------------------------------
	| Statistics page
	|--------------------------------------------------------------------------
	*/
	'title' => '统计',

	'preview_text' => '这是 Lychee <span class="text-primary-emphasis font-bold">SE</span> 版本中统计页面的预览。<br />此处显示的数据是随机生成的，并不反映您的服务器实际情况。',
	'no_data' => '用户在服务器上没有数据。',
	'collapse' => '折叠相册大小',

	'total' => [
		'total' => '总计',
		'albums' => '相册',
		'photos' => '照片',
		'size' => '大小',
	],
	'table' => [
		'username' => '所有者',
		'title' => '标题',
		'photos' => '照片',
		'descendants' => '子项',
		'size' => '大小',
	],
	'punch_card' => [
		'title' => '活动',
		'photo-taken' => '拍摄了 %d 张照片',
		'photo-taken-in' => '%2$d 年拍摄了 %1$d 张照片',
		'photo-uploaded' => '上传了 %d 张照片',
		'photo-uploaded-in' => '%2$d 年上传了 %1$d 张照片',
		'with-exif' => '包含 EXIF 数据',
		'less' => '较少',
		'more' => '较多',
		'tooltip' => '%2$s 有 %1$d 张照片',
		'created_at' => '上传日期',
		'taken_at' => 'EXIF 日期',
		'caption' => '每列代表一周。',
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