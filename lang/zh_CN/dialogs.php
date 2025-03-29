<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

return [
	/*
	|--------------------------------------------------------------------------
	| Dialogs
	|--------------------------------------------------------------------------
	*/
	'button' => [
		'close' => '关闭',
		'cancel' => '取消',
		'save' => '保存',
		'delete' => '删除',
		'move' => '移动',
	],
	'about' => [
		'subtitle' => '专业的自托管照片管理工具',
		'description' => 'Lychee 是一个免费的照片管理工具，可以运行在您的服务器或网络空间上。安装过程只需几秒钟。您可以像使用本地应用程序一样上传、管理和分享照片。Lychee 提供您所需的一切功能，并安全地存储您的所有照片。',
		'update_available' => '有可用更新！',
		'thank_you' => '感谢您的支持！',
		'get_supporter_or_register' => '获取独家功能并支持 Lychee 的开发。<br />解锁 <a href="https://lycheeorg.dev/get-supporter-edition/" class="text-primary-500 underline">支持者版本</a> 或注册您的许可证密钥',
		'here' => '在这里',
	],
	'dropbox' => [
		'not_configured' => 'Dropbox 未配置。',
	],
	'import_from_link' => [
		'instructions' => '请输入照片的直接链接以导入：',
		'import' => '导入',
	],
	'keybindings' => [
		'header' => 'Keyboard shortcuts',
		'don_t_show_again' => '不再显示',
		'hide_header_button' => 'Don\'t show help in header',
		'side_wide' => '全局快捷键',
		'back_cancel' => '返回/取消',
		'confirm' => '确认',
		'login' => '登录',
		'toggle_full_screen' => '切换全屏',
		'toggle_sensitive_albums' => '切换敏感相册显示',

		'albums' => '相册快捷键',
		'new_album' => '新建相册',
		'upload_photos' => '上传照片',
		'search' => '搜索',
		'show_this_modal' => '显示此窗口',
		'select_all' => '全选',
		'move_selection' => '移动所选',
		'delete_selection' => '删除所选',

		'album' => '相册快捷键',
		'slideshow' => '开始/停止幻灯片',
		'toggle' => '切换面板',

		'photo' => '照片快捷键',
		'previous' => '上一张',
		'next' => '下一张',
		'cycle' => '循环显示模式',
		'star' => '标星照片',
		'move' => '移动照片',
		'delete' => '删除照片',
		'edit' => '编辑信息',
		'show_hide_meta' => '显示信息',

		'keep_hidden' => '我们会保持隐藏。',
		'button_hidden' => 'We will hide the button in the header.',
	],
	'login' => [
		'username' => '用户名',
		'password' => '密码',
		'unknown_invalid' => '用户名不存在或密码错误',
		'signin' => '登录',
	],
	'register' => [
		'enter_license' => '请在下方输入您的许可证密钥：',
		'license_key' => '许可证密钥',
		'invalid_license' => '无效的许可证密钥。',
		'register' => '注册',
	],
	'share_album' => [
		'url_copied' => '链接已复制到剪贴板！',
	],
	'upload' => [
		'completed' => '已完成',
		'uploaded' => '已上传：',
		'release' => '松开文件开始上传！',
		'select' => '点击此处选择要上传的文件',
		'drag' => '（或将文件拖到页面上）',
		'loading' => '加载中',
		'resume' => '继续',
		'uploading' => '上传中',
		'finished' => '已完成',
		'failed_error' => '上传失败。服务器返回错误！',
	],
	'visibility' => [
		'public' => '公开',
		'public_expl' => '匿名用户可以访问此相册，但受以下限制。',
		'full' => '原图',
		'full_expl' => '匿名用户可以查看原始分辨率的照片。',
		'hidden' => '隐藏',
		'hidden_expl' => '匿名用户需要直接链接才能访问此相册。',
		'downloadable' => '可下载',
		'downloadable_expl' => '匿名用户可以下载此相册。',
		'upload' => 'Allow uploads',
		'upload_expl' => '<i class="pi pi-exclamation-triangle text-warning-700 mr-1"></i> Anonymous users can upload photos to this album.',
		'password' => '密码',
		'password_prot' => '密码保护',
		'password_prot_expl' => '匿名用户需要共享密码才能访问此相册。',
		'password_prop_not_compatible' => '此设置与响应缓存机制存在冲突。<br>由于启用了响应缓存，一旦解锁此相册，<br>其他匿名用户也将能够看到相册内容。',
		'nsfw' => '敏感内容',
		'nsfw_expl' => '相册包含敏感内容。',
		'visibility_updated' => '可见性已更新。',
	],
	'move_album' => [
		'confirm_single' => '您确定要将相册"%1$s"移动到相册"%2$s"吗？',
		'confirm_multiple' => '您确定要将所有选定的相册移动到相册"%s"吗？',
		'move_single' => '移动相册',
		'move_to' => '移动到',
		'move_to_single' => '将 %s 移动到：',
		'move_to_multiple' => '将 %d 个相册移动到：',
		'no_album_target' => '没有可移动到的相册',
		'moved_single' => '相册已移动！',
		'moved_single_details' => '%1$s 已移动到 %2$s',
		'moved_details' => '相册已移动到 %s',
	],
	'new_album' => [
		'menu' => '创建相册',
		'info' => '请输入新相册的标题：',
		'title' => '标题',
		'create' => '创建相册',
	],
	'new_tag_album' => [
		'menu' => '创建标签相册',
		'info' => '请输入新标签相册的标题：',
		'title' => '标题',
		'set_tags' => '设置要显示的标签',
		'warn' => '请确保每个标签后按回车键',
		'create' => '创建标签相册',
	],
	'delete_album' => [
		'confirmation' => '您确定要删除相册"%s"及其包含的所有照片吗？',
		'confirmation_multiple' => '您确定要删除所有 %d 个选定的相册及其包含的所有照片吗？',
		'warning' => '此操作无法撤销！',
		'delete' => '删除相册和照片',
	],
	'transfer' => [
		'query' => '将相册所有权转移给',
		'confirmation' => '您确定要将相册"%s"及其包含的所有照片的所有权转移给"%s"吗？',
		'lost_access_warning' => '您将失去对此相册的访问权限。',
		'warning' => '此操作无法撤销！',
		'transfer' => '转移相册和照片的所有权',
	],
	'rename' => [
		'photo' => '请输入此照片的新标题：',
		'album' => '请输入此相册的新标题：',
		'rename' => '重命名',
	],
	'merge' => [
		'merge_to' => '将 %s 合并到：',
		'merge_to_multiple' => '将 %d 个相册合并到：',
		'no_albums' => '没有可合并的相册。',
		'confirm' => '您确定要将相册"%1$s"合并到相册"%2$s"吗？',
		'confirm_multiple' => '您确定要将所有选定的相册合并到相册"%s"吗？',
		'merge' => '合并相册',
		'merged' => '相册已合并到 %s！',
	],
	'unlock' => [
		'password_required' => '此相册受密码保护。请在下方输入密码以查看相册中的照片：',
		'password' => '密码',
		'unlock' => '解锁',
	],
	'photo_tags' => [
		'question' => '为此照片输入标签。',
		'question_multiple' => '为所有 %d 张选定的照片输入标签。现有标签将被覆盖。',
		'no_tags' => '无标签',
		'set_tags' => '设置标签',
		'updated' => '标签已更新！',
		'tags_override_info' => '如果取消选中此项，标签将添加到照片的现有标签中。',
	],
	'photo_copy' => [
		'no_albums' => '没有可复制到的相册',
		'copy_to' => '将 %s 复制到：',
		'copy_to_multiple' => '将 %d 张照片复制到：',
		'confirm' => '将 %s 复制到 %s。',
		'confirm_multiple' => '将 %d 张照片复制到 %s。',
		'copy' => '复制',
		'copied' => '照片已复制！',
	],
	'photo_delete' => [
		'confirm' => '您确定要删除照片"%s"吗？',
		'confirm_multiple' => '您确定要删除所有 %d 张选定的照片吗？',
		'deleted' => '照片已删除！',
	],
	'move_photo' => [
		'move_single' => '将 %s 移动到：',
		'move_multiple' => '将 %d 张照片移动到：',
		'confirm' => '将 %s 移动到 %s。',
		'confirm_multiple' => '将 %d 张照片移动到 %s。',
		'moved' => '照片已移动到 %s！',
	],
	'target_user' => [
		'placeholder' => '选择用户',
	],
	'target_album' => [
		'placeholder' => '选择相册',
	],
	'webauthn' => [
		'u2f' => 'U2F',
		'success' => '认证成功！',
		'error' => '抱歉，似乎出现了问题。请刷新页面并重试！',
	],
	'se' => [
		'available' => '支持者版本可用',
	],
	'session_expired' => [
		'title' => '会话已过期',
		'message' => '您的会话已过期。<br />请刷新页面。',
		'reload' => '刷新',
		'go_to_gallery' => '返回相册',
	],
];