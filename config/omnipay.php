<?php

return [
	/*
	|--------------------------------------------------------------------------
	| Default Omnipay configuration options
	|--------------------------------------------------------------------------
	*/
	'gateway' => env('OMNIPAY_GATEWAY', 'Dummy'),

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
		'apiKey' => env('MOLLIE_API_KEY', ''),
	],

	'Stripe' => [
		'apiKey' => env('STRIPE_API_KEY', ''),
	],

	// https://github.com/thephpleague/omnipay-paypal/blob/master/src/RestGateway.php
	'PayPal_Rest' => [
		'clientId' => env('PAYPAL_CLIENT_ID', ''),
		'secret' => env('PAYPAL_SECRET', ''),
		'testMode' => (bool) env('PAYPAL_TEST_MODE', false),
	],

	// https://github.com/thephpleague/omnipay-paypal/blob/master/src/ProGateway.php
	'PayPal_Pro' => [
		'username' => env('PAYPAL_API_USERNAME', ''),
		'password' => env('PAYPAL_API_PASSWORD', ''),
		'signature' => env('PAYPAL_API_SIGNATURE', ''),
		'testMode' => (bool) env('PAYPAL_TEST_MODE', false),
	],

	// https://github.com/thephpleague/omnipay-paypal/blob/master/src/ExpressGateway.php
	'PayPal_Express' => [
		'username' => env('PAYPAL_API_USERNAME', ''),
		'password' => env('PAYPAL_API_PASSWORD', ''),
		'signature' => env('PAYPAL_API_SIGNATURE', ''),
		'testMode' => (bool) env('PAYPAL_TEST_MODE', false),

		'solutionType' => 'Sole', // array('Sole', 'Mark'),
		'landingPage' => 'Billing', // array('Billing', 'Login'),
		'brandName' => '',
		'headerImageUrl' => '',
		'logoImageUrl' => '',
		'borderColor' => '',
	],

	// https://github.com/thephpleague/omnipay-paypal/blob/master/src/ExpressInContextGateway.php
	'PayPal_ExpressInContext' => [
		'username' => env('PAYPAL_API_USERNAME', ''),
		'password' => env('PAYPAL_API_PASSWORD', ''),
		'signature' => env('PAYPAL_API_SIGNATURE', ''),
		'testMode' => (bool) env('PAYPAL_TEST_MODE', false),

		'solutionType' => 'Sole', // array('Sole', 'Mark'),
		'landingPage' => 'Billing', // array('Billing', 'Login'),
		'brandName' => '',
		'headerImageUrl' => '',
		'logoImageUrl' => '',
		'borderColor' => '',
	],
];
