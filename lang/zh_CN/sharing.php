<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

return [
	/*
	|--------------------------------------------------------------------------
	| Sharing page
	|--------------------------------------------------------------------------
	*/
	'title' => '共享',

	'info' => '此页面提供相册共享权限的概览和编辑功能。',
	'album_title' => '相册标题',
	'username' => '用户名',
	'no_data' => '共享列表为空。',
	'share' => '共享',
	'add_new_access_permission' => '添加新的访问权限',
	'permission_deleted' => '权限已删除！',
	'permission_created' => '权限已创建！',
	'propagate' => '传播',

	'propagate_help' => '将当前访问权限传播到所有子项<br>（子相册及其各自的子相册等）',
	'propagate_default' => '默认情况下，现有权限（相册-用户）<br>将被更新，并添加缺失的权限。<br>此列表中不存在的其他权限将保持不变。',
	'propagate_overwrite' => '覆盖现有权限而不是更新。<br>这也将删除此列表中不存在的所有权限。',
	'propagate_warning' => '此操作无法撤销。',

	'permission_overwritten' => '传播成功！权限已覆盖！',
	'permission_updated' => '传播成功！权限已更新！',
	'bluk_share' => 'Bulk share',
	'bulk_share_instr' => 'Select multiple albums and users to share with.',
	'albums' => 'Albums',
	'users' => 'Users',
	'no_users' => 'No selectable users.',
	'no_albums' => 'No selectable albums.',

	'grants' => [
		'read' => '授予读取权限',
		'original' => '授予访问原始照片的权限',
		'download' => '授予下载权限',
		'upload' => '授予上传权限',
		'edit' => '授予编辑权限',
		'delete' => '授予删除权限',
	],
];