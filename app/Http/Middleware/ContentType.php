<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Http\Middleware;

use App\Exceptions\Internal\LycheeInvalidArgumentException;
use App\Exceptions\UnexpectedContentType;
use Illuminate\Http\Request;

/**
 * Class ContentType.
 *
 * This middleware ensures that the request has sent the correct content type
 * The supported parameters are `:json` and `:multipart'.
 */
class ContentType
{
	public const JSON = 'json';
	public const MULTIPART = 'multipart';

	/**
	 * Handles the incoming request.
	 *
	 * @param Request  $request      the incoming request to serve
	 * @param \Closure $next         the next operation to be applied to the request
	 * @param string   $content_type the content type of the request's body;
	 *                               either {@link ContentType::JSON} or {@link ContentType::MULTIPART}
	 *
	 * @throws UnexpectedContentType
	 * @throws LycheeInvalidArgumentException
	 */
	public function handle(Request $request, \Closure $next, string $content_type): mixed
	{
		// Skip if check is disabled
		if (config('features.require-content-type') === false) {
			return $next($request);
		}

		if ($content_type === self::JSON) {
			if (!$request->isJson()) {
				throw new UnexpectedContentType(self::JSON);
			}
		} elseif ($content_type === self::MULTIPART) {
			if ($request->getContentTypeFormat() !== 'form') {
				throw new UnexpectedContentType(self::MULTIPART);
			}
		} else {
			throw new LycheeInvalidArgumentException('$contentType must either be "' . self::JSON . '" or "' . self::MULTIPART . '"');
		}

		return $next($request);
	}
}