<?php

return [
	/*
	|--------------------------------------------------------------------------
	| Default Omnipay configuration options
	|--------------------------------------------------------------------------
	*/
	'gateway' => env('OMNIPAY_GATEWAY', 'Dummy'),

	/**
	 * Enable or disable test mode for Omnipay gateways.
	 */
	'testMode' => (bool) env('OMNIPAY_TEST_MODE', false),

	/**
	 * Dummy gateway configuration to simulate payments in test mode.
	 */
	'Dummy' => [
		'apiKey' => 'dummy',
	],

	/**
	 * Mollie gateway configuration.
	 */
	'Mollie' => [
		'profileId' => env('MOLLIE_PROFILE_ID', ''),
		'apiKey' => env('MOLLIE_API_KEY', ''),
	],

	/**
	 * Stripe gateway configuration (NOT ACTIVE YET).
	 */
	'Stripe' => [
		'publishableKey' => env('STRIPE_PUBLISHABLE_KEY', ''),
		'apiKey' => env('STRIPE_API_KEY', ''),
	],

	/**
	 * PayPal gateway configuration.
	 */
	'PayPal' => [
		'clientId' => env('PAYPAL_CLIENT_ID', ''),
		'secret' => env('PAYPAL_SECRET', ''),
	],
];
