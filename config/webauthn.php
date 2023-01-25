<?php

return [
	/*
	|--------------------------------------------------------------------------
	| Relying Party
	|--------------------------------------------------------------------------
	|
	| We will use your application information to inform the device who is the
	| relying party. While only the name is enough, you can further set the
	| a custom domain as ID and even an icon image data encoded as BASE64.
	|
	*/

	'relying_party' => [
		'name' => env('WEBAUTHN_NAME', env('APP_NAME', 'Lychee')),
		'id' => env('WEBAUTHN_ID'),
	],

	/*
	|--------------------------------------------------------------------------
	| Challenge configuration
	|--------------------------------------------------------------------------
	|
	| When making challenges your application needs to push at least 16 bytes
	| of randomness. Since we need to later check them, we'll also store the
	| bytes for a small amount of time inside this current request session.
	|
	*/

	'challenge' => [
		'bytes' => 16,
		'timeout' => 60,
		'key' => '_webauthn',
	],
];
