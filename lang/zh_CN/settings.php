<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

return [
	/*
	|--------------------------------------------------------------------------
	| Settings page
	|--------------------------------------------------------------------------
	*/
	'title' => '设置',
	'small_screen' => '为了获得更好的设置页面体验，<br />建议您使用更大的屏幕。',
	'tabs' => [
		'basic' => '基本',
		'all_settings' => '所有设置',
	],
	'toasts' => [
		'change_saved' => '更改已保存！',
		'details' => '设置已按要求修改',
		'error' => '错误！',
		'error_load_css' => '无法加载 dist/user.css',
		'error_load_js' => '无法加载 dist/custom.js',
		'error_save_css' => '无法保存 CSS',
		'error_save_js' => '无法保存 JS',
		'thank_you' => '感谢您的支持。',
		'reload' => '请刷新页面以获得完整功能。',
	],
	'system' => [
		'header' => '系统',
		'use_dark_mode' => '使用 Lychee 深色模式',
		'language' => 'Lychee 使用的语言',
		'nsfw_album_visibility' => '默认显示敏感相册。',
		'nsfw_album_explanation' => '如果相册是公开的，它仍然可以访问，只是被隐藏起来，<b>可以通过按 <kbd>H</kbd> 键显示</b>。',
		'cache_enabled' => '启用响应缓存',
		'cache_enabled_details' => '这将显著提高 Lychee 的响应速度。<br> <i class="pi pi-exclamation-triangle text-warning-600 mr-2"></i>如果您使用了密码保护的相册，建议不要启用此功能。',
	],
	'lychee_se' => [
		'header' => 'Lychee <span class="text-primary-emphasis">SE</span>',
		'call4action' => '获取独家功能并支持 Lychee 的开发。解锁 <a href="https://lycheeorg.dev/get-supporter-edition/" class="text-primary-500 underline">支持者版本</a>。',
		'preview' => '启用 Lychee SE 功能预览',
		'hide_call4action' => '隐藏 Lychee SE 注册表单。我对现有的 Lychee 很满意。:)',
		'hide_warning' => '启用后，注册许可证密钥的唯一方式将是通过上方的更多选项卡。更改将在页面刷新后生效。',
	],
	'dropbox' => [
		'header' => 'Dropbox',
		'instruction' => '要从 Dropbox 导入照片，您需要从其网站获取有效的 drop-ins 应用密钥。',
		'api_key' => 'Dropbox API 密钥',
		'set_key' => '设置 Dropbox 密钥',
	],
	'gallery' => [
		'header' => '相册',
		'photo_order_column' => '照片排序默认列',
		'photo_order_direction' => '照片排序默认方向',
		'album_order_column' => '相册排序默认列',
		'album_order_direction' => '相册排序默认方向',
		'aspect_ratio' => '相册缩略图默认宽高比',
		'photo_layout' => '图片布局',
		'album_decoration' => '在相册封面显示装饰（子相册 和/或 照片数量）',
		'album_decoration_direction' => '相册装饰水平或垂直对齐',
		'photo_overlay' => '默认图片覆盖信息',
		'license_default' => '相册默认许可证',
		'license_help' => '需要帮助选择？',
	],
	'geolocation' => [
		'header' => '地理位置',
		'map_display' => '显示 GPS 坐标对应的地图',
		'map_display_public' => '允许匿名用户访问地图',
		'map_provider' => '设置地图提供商',
		'map_include_subalbums' => '在地图上包含子相册的照片',
		'location_decoding' => '使用 GPS 位置解码',
		'location_show' => '显示从 GPS 坐标提取的位置',
		'location_show_public' => '匿名用户可以访问从 GPS 坐标提取的位置',
	],
	'advanced' => [
		'header' => '高级自定义',
		'change_css' => '修改 CSS',
		'change_js' => '修改 JS',
	],
	'all' => [
		'old_setting_style' => '旧设置样式',
		'change_detected' => '部分设置已更改。',
		'save' => '保存',
	],

	'tool_option' => [
		'disabled' => '已禁用',
		'enabled' => '已启用',
		'discover' => '发现',
	],
];