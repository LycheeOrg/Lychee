<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2018-2025 LycheeOrg.
 */

return [
	/*
	|--------------------------------------------------------------------------
	| Profile page
	|--------------------------------------------------------------------------
	*/
	'title' => '个人资料',

	'login' => [
		'header' => '个人资料',
		'enter_current_password' => '请输入您的当前密码：',
		'current_password' => '当前密码',
		'credentials_update' => '您的登录信息将更改为：',
		'username' => '用户名',
		'new_password' => '新密码',
		'confirm_new_password' => '确认新密码',
		'email_instruction' => '添加您的邮箱以启用邮件通知。如需停止接收邮件，只需删除下方的邮箱地址即可。',
		'email' => '邮箱',
		'change' => '修改登录信息',
		'api_token' => 'API 令牌...',

		'missing_fields' => '缺少必填项',
	],

	'token' => [
		'unavailable' => '您已查看过此令牌。',
		'no_data' => '尚未生成 API 令牌。',
		'disable' => '禁用',
		'disabled' => '令牌已禁用',
		'warning' => '此令牌不会再次显示。请复制并将其保存在安全的地方。',
		'reset' => '重置令牌',
		'create' => '创建新令牌',
	],

	'oauth' => [
		'header' => 'OAuth',
		'header_not_available' => 'OAuth 不可用',
		'setup_env' => '在 .env 文件中设置凭据',
		'token_registered' => '%s 令牌已注册。',
		'setup' => '设置 %s',
		'reset' => '重置',
		'credential_deleted' => '凭据已删除！',
	],

	'u2f' => [
		'header' => 'Passkey/MFA/2FA',
		'info' => '这仅提供使用 WebAuthn 进行身份验证的功能，以替代用户名和密码。',
		'empty' => '凭据列表为空！',
		'not_secure' => '环境不安全。U2F 不可用。',
		'new' => '注册新设备',
		'credential_deleted' => '凭据已删除！',
		'credential_updated' => '凭据已更新！',
		'credential_registred' => '注册成功！',
		'5_chars' => '至少需要 5 个字符。',
	],
];