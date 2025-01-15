<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

return [
	/*
	|--------------------------------------------------------------------------
	| Profile page
	|--------------------------------------------------------------------------
	*/
	'title' => 'Profile',

	'login' => [
		'header' => 'Profile',
		'enter_current_password' => 'Enter your current password:',
		'current_password' => 'Current password',
		'credentials_update' => 'Your credentials will be changed to the following:',
		'username' => 'Username',
		'new_password' => 'New password',
		'confirm_new_password' => 'Confirm new password',
		'email_instruction' => 'Add your email below to enable receiving email notifications. To stop receiving emails, simply remove your email below.',
		'email' => 'Email',
		'change' => 'Change Login',
		'api_token' => 'API Token ...',

		'missing_fields' => 'Missing fields',
	],

	'token' => [
		'unavailable' => 'You have already viewed this token.',
		'no_data' => 'No token API have been generated.',
		'disable' => 'Disable',
		'disabled' => 'Token disabled',
		'warning' => 'This token will not be displayed again. Copy it and keep it in a safe place.',
		'reset' => 'Reset the token',
		'create' => 'Create a new token',
	],

	'oauth' => [
		'header' => 'OAuth',
		'header_not_available' => 'OAuth is not available',
		'setup_env' => 'Set up the credentials in your .env',
		'token_registered' => '%s token registered.',
		'setup' => 'Set up %s',
		'reset' => 'reset',
		'credential_deleted' => 'Credential deleted!',
	],

	'u2f' => [
		'header' => 'Passkey/MFA/2FA',
		'info' => 'This only provides the ability to use WebAuthn to authenticate instead of username & password.',
		'empty' => 'Credentials list is empty!',
		'not_secure' => 'Environment not secured. U2F not available.',
		'new' => 'Register new device.',
		'credential_deleted' => 'Credential deleted!',
		'credential_updated' => 'Credential updated!',
		'credential_registred' => 'Registration successful!',
		'5_chars' => 'At least 5 chars.',
	],
];