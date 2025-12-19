<?php

return [
	/*
	|--------------------------------------------------------------------------
	| Default Omnipay configuration options
	|--------------------------------------------------------------------------
	*/
	'gateway' => env('OMNIPAY_GATEWAY', 'Dummy'),

	'testMode' => (bool) env('OMNIPAY_TEST_MODE', false),

	// case DUMMY = 'Dummy';
	// case MOLLIE = 'Mollie';
	// case PAYPAL_EXPRESS = 'PayPal_Express';
	// case PAYPAL_EXPRESSINCONTEXT = 'PayPal_ExpressInContext';
	// case PAYPAL_PRO = 'PayPal_Pro';
	// case PAYPAL_REST = 'PayPal_Rest';
	// case STRIPE = 'Stripe';
	'Dummy' => [
		'apiKey' => 'dummy',
	],

	'Mollie' => [
		'profileId' => env('MOLLIE_PROFILE_ID', ''),
		'apiKey' => env('MOLLIE_API_KEY', ''),
	],

	'Stripe' => [
		'publishableKey' => env('STRIPE_PUBLISHABLE_KEY', ''),
		'apiKey' => env('STRIPE_API_KEY', ''),
	],

	'PayPal' => [
		'clientId' => env('PAYPAL_CLIENT_ID', ''),
		'secret' => env('PAYPAL_SECRET', ''),
		'testMode' => (bool) env('OMNIPAY_TEST_MODE', false),
	],
];
