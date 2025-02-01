<?php

use App\Http\Resources\OpenApi\DataToResponse;

return [
	/*
	 * Your API path. By default, all routes starting with this path will be added to the docs.
	 * If you need to change this behavior, you can add your custom routes resolver using `Scramble::routes()`.
	 */
	'api_path' => 'api/v2',

	/*
	 * Your API domain. By default, app domain is used. This is also a part of the default API routes
	 * matcher, so when implementing your own, make sure you use this config if needed.
	 */
	'api_domain' => null,

	/*
	 * The path where your OpenAPI specification will be exported.
	 */
	'export_path' => 'api.json',

	'info' => [
		/*
		 * API version.
		 */
		'version' => env('API_VERSION', '2.0.0'),

		/*
		 * Description rendered on the home page of the API documentation (`/docs/api`).
		 */
		'description' => '**NOTE:** In order to use the API from this page (with the Send API Request button), you will need to disable the _"content-type"_ middleware.<br>
		This is done by setting your `REQUIRE_CONTENT_TYPE_ENABLED=false` in your `.env`.<br>
		After testing, we recommend setting back this value to `true` and adding the content-type header to your requests.',
	],

	/*
	 * Customize Stoplight Elements UI
	 */
	'ui' => [
		/*
		 * Define the title of the documentation's website. App name is used when this config is `null`.
		 */
		'title' => null,

		/*
		 * Define the theme of the documentation. Available options are `light` and `dark`.
		 */
		'theme' => 'light',

		/*
		 * Hide the `Try It` feature. Enabled by default.
		 */
		'hide_try_it' => false,

		/*
		 * URL to an image that displays as a small square logo next to the title, above the table of contents.
		 */
		'logo' => '',

		/*
		 * Use to fetch the credential policy for the Try It feature. Options are: omit, include (default), and same-origin
		 */
		'try_it_credentials_policy' => 'include',
	],

	/*
	 * The list of servers of the API. By default, when `null`, server URL will be created from
	 * `scramble.api_path` and `scramble.api_domain` config variables. When providing an array, you
	 * will need to specify the local server URL manually (if needed).
	 *
	 * Example of non-default config (final URLs are generated using Laravel `url` helper):
	 *
	 * ```php
	 * 'servers' => [
	 *     'Live' => 'api',
	 *     'Prod' => 'https://scramble.dedoc.co/api',
	 * ],
	 * ```
	 */
	'servers' => null,

	'middleware' => [
		'web',
		// RestrictedDocsAccess::class,
	],

	'extensions' => [
		DataToResponse::class,
	],
];
