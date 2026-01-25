<?php

return [
	/*
	|--------------------------------------------------------------------------
	| LDAP Connection Configuration
	|--------------------------------------------------------------------------
	|
	| This array holds the LDAP server connection details and authentication
	| settings. All values are loaded from environment variables for security.
	|
	*/

	'connections' => [
		'default' => [
			// LDAP server connection details
			'hosts' => [env('LDAP_HOST', 'ldap.example.com')],
			'port' => env('LDAP_PORT', 389),
			'base_dn' => env('LDAP_BASE_DN', 'dc=example,dc=com'),
			'username' => env('LDAP_BIND_DN', 'cn=bind-user,dc=example,dc=com'),
			'password' => env('LDAP_BIND_PASSWORD', ''),

			// Connection timeout (seconds)
			'timeout' => env('LDAP_CONNECTION_TIMEOUT', 5),

			// TLS/SSL settings
			'use_tls' => env('LDAP_USE_TLS', true),
			'use_ssl' => env('LDAP_PORT', 389) === 636, // Auto-detect LDAPS from port

			// Additional connection options
			'options' => [
				// TLS certificate verification
				LDAP_OPT_X_TLS_REQUIRE_CERT => env('LDAP_TLS_VERIFY_PEER', true)
					? LDAP_OPT_X_TLS_DEMAND
					: LDAP_OPT_X_TLS_ALLOW,
			],
		],
	],

	/*
	|--------------------------------------------------------------------------
	| LDAP Authentication Configuration
	|--------------------------------------------------------------------------
	|
	| Controls whether LDAP authentication is enabled and how users are
	| provisioned into the Lychee database.
	|
	*/

	'auth' => [
		// Enable LDAP authentication
		'enabled' => env('LDAP_ENABLED', false),

		// Auto-provision users on first LDAP login
		'auto_provision' => env('LDAP_AUTO_PROVISION', true),

		// LDAP user search filter (%s is replaced with username)
		// OpenLDAP: (&(objectClass=person)(uid=%s))
		// Active Directory: (&(objectClass=user)(sAMAccountName=%s))
		'user_filter' => env('LDAP_USER_FILTER', '(&(objectClass=person)(uid=%s))'),

		// LDAP attribute mapping to Lychee user fields
		'attributes' => [
			'username' => env('LDAP_ATTR_USERNAME', 'uid'),
			'email' => env('LDAP_ATTR_EMAIL', 'mail'),
			'display_name' => env('LDAP_ATTR_DISPLAY_NAME', 'displayName'),
		],

		// Admin role assignment
		'admin_group_dn' => env('LDAP_ADMIN_GROUP_DN', null),
	],

	/*
	|--------------------------------------------------------------------------
	| LDAP Logging Configuration
	|--------------------------------------------------------------------------
	|
	| Configure logging for LDAP operations. Sensitive data (passwords)
	| should never be logged.
	|
	*/

	'logging' => env('LDAP_LOGGING', false),
];
