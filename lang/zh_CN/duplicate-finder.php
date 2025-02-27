<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

return [
	/*
	|--------------------------------------------------------------------------
	| Duplicate Finder Page
	|--------------------------------------------------------------------------
	*/
	'title' => '维护',
	'intro' => '在此页面中，您可以查看数据库中发现的重复照片。',
	'found' => ' 个重复项！',
	'invalid-search' => ' 至少需要选择校验和或标题条件之一。',
	'checksum-must-match' => '校验和必须匹配。',
	'title-must-match' => '标题必须匹配。',
	'must-be-in-same-album' => '必须在同一相册中。',

	'columns' => [
		'album' => '相册',
		'photo' => '照片',
		'checksum' => '校验和',
	],

	'warning' => [
		'no-original-left' => '没有原始文件。',
		'keep-one' => '您选择了此组中的所有重复项。请至少保留一个副本。',
	],

	'delete-selected' => '删除所选',
];