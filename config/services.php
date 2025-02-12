<?php

return [
	/*
	|--------------------------------------------------------------------------
	| Third Party Services
	|--------------------------------------------------------------------------
	|
	| This file is for storing the credentials for third party services such
	| as Mailgun, Postmark, AWS and more. This file provides the de facto
	| location for this type of information, allowing packages to have
	| a conventional file to locate the various service credentials.
	|
	*/

	'mailgun' => [
		'domain' => env('MAILGUN_DOMAIN'),
		'secret' => env('MAILGUN_SECRET'),
		'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
	],

	'postmark' => [
		'token' => env('POSTMARK_TOKEN'),
	],

	'ses' => [
		'key' => env('AWS_ACCESS_KEY_ID'),
		'secret' => env('AWS_SECRET_ACCESS_KEY'),
		'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
	],

	/*
	|--------------------------------------------------------------------------
	| Oauth services
	|--------------------------------------------------------------------------
	*/
	'amazon' => [
		'client_id' => env('AMAZON_SIGNIN_CLIENT_ID'),
		'client_secret' => env('AMAZON_SIGNIN_SECRET'),
		'redirect' => env('AMAZON_SIGNIN_REDIRECT_URI', '/auth/amazon/redirect'),
	],

	// https://developer.okta.com/blog/2019/06/04/what-the-heck-is-sign-in-with-apple
	// Note: the client secret used for "Sign In with Apple" is a JWT token that can have a maximum lifetime of 6 months.
	// The article above explains how to generate the client secret on demand and you'll need to update this every 6 months.
	// To generate the client secret for each request, see Generating A Client Secret For Sign In With Apple On Each Request.
	// https://bannister.me/blog/generating-a-client-secret-for-sign-in-with-apple-on-each-request
	'apple' => [
		'client_id' => env('APPLE_CLIENT_ID'),
		'client_secret' => env('APPLE_CLIENT_SECRET'),
		'redirect' => env('APPLE_REDIRECT_URI', '/auth/apple/redirect'),
	],

	'authelia' => [
		'client_id' => env('AUTHELIA_CLIENT_ID'),
		'client_secret' => env('AUTHELIA_CLIENT_SECRET'),
		'redirect' => env('AUTHELIA_REDIRECT_URI'),
		'base_url' => env('AUTHELIA_BASE_URL'),
	],

	'authentik' => [
		'client_id' => env('AUTHENTIK_CLIENT_ID'),
		'client_secret' => env('AUTHENTIK_CLIENT_SECRET'),
		'redirect' => env('AUTHENTIK_REDIRECT_URI'),
		'base_url' => env('AUTHENTIK_BASE_URL'),
	],

	'facebook' => [
		'client_id' => env('FACEBOOK_CLIENT_ID'),
		'client_secret' => env('FACEBOOK_CLIENT_SECRET'),
		'redirect' => env('FACEBOOK_REDIRECT_URI', '/auth/facebook/redirect'),
	],

	'github' => [
		'client_id' => env('GITHUB_CLIENT_ID'),
		'client_secret' => env('GITHUB_CLIENT_SECRET'),
		'redirect' => env('GITHUB_REDIRECT_URI', '/auth/github/redirect'),
	],

	'google' => [
		'client_id' => env('GOOGLE_CLIENT_ID'),
		'client_secret' => env('GOOGLE_CLIENT_SECRET'),
		'redirect' => env('GOOGLE_REDIRECT_URI', '/auth/google/redirect'),
	],

	'mastodon' => [
		'domain' => env('MASTODON_DOMAIN'),
		'client_id' => env('MASTODON_ID'),
		'client_secret' => env('MASTODON_SECRET'),
		'redirect' => env('MASTODON_REDIRECT_URI', '/auth/mastodon/redirect'),
		// 'read', 'write', 'follow'
		'scope' => ['read'],
	],

	'microsoft' => [
		'client_id' => env('MICROSOFT_CLIENT_ID'),
		'client_secret' => env('MICROSOFT_CLIENT_SECRET'),
		'redirect' => env('MICROSOFT_REDIRECT_URI', '/auth/microsoft/redirect'),
	],

	'nextcloud' => [
		'client_id' => env('NEXTCLOUD_CLIENT_ID'),
		'client_secret' => env('NEXTCLOUD_CLIENT_SECRET'),
		'redirect' => env('NEXTCLOUD_REDIRECT_URI', '/auth/nextcloud/redirect'),
		'instance_uri' => env('NEXTCLOUD_BASE_URI'),
	],
	'keycloak' => [
		'client_id' => env('KEYCLOAK_CLIENT_ID'),
		'client_secret' => env('KEYCLOAK_CLIENT_SECRET'),
		'redirect' => env('KEYCLOAK_REDIRECT_URI'),
		'base_url' => env('KEYCLOAK_BASE_URL'),
		'realms' => env('KEYCLOAK_REALM'),
	],
];
