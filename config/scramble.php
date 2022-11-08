<?php

use App\Assets\DTOResponseTypeScrambleExtension;
use Dedoc\Scramble\Http\Middleware\RestrictedDocsAccess;

return [
	/*
	 * Your API path. Full API base URL will be created using `url` helper: `url(config('scramble.api_path'))`.
	 * By default, all routes starting with this path will be added to the docs. If you need to change
	 * this behavior, you can add your custom routes resolver using `Scramble::routes()`.
	 */
	'api_path' => 'api',

	'info' => [
		/*
		 * API version.
		 */
		'version' => env('API_VERSION', '0.0.1'),

		/*
		 * Description rendered on the home page of the API documentation (`/docs/api`).
		 */
		'description' => '',
	],

	'middleware' => [
		'web',
		RestrictedDocsAccess::class,
	],

	'extensions' => [
		DTOResponseTypeScrambleExtension::class,
	],
];
