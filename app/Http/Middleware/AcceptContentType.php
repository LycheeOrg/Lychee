<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Http\Middleware;

use App\Exceptions\Internal\LycheeInvalidArgumentException;
use App\Exceptions\UnexpectedContentType;
use Illuminate\Http\Request;

/**
 * Class AcceptContentType.
 *
 * This middleware ensures that the client also accepts a suitable content
 * type as a response.
 * The supported parameters are `:json`, `:html` and `:any'.
 */
class AcceptContentType
{
	public const ANY = 'any';
	public const JSON = 'json';
	public const HTML = 'html';

	/**
	 * The URIs that should be excluded from AcceptContentType verification.
	 *
	 * @var array<int,string>
	 */
	protected $except = [
		'/feed',
	];

	/**
	 * Handles the incoming request.
	 *
	 * @param Request  $request      the incoming request to serve
	 * @param \Closure $next         the next operation to be applied to the request
	 * @param string   $content_type the content type which must be acceptable by the client;
	 *                               either {@link self::JSON}, {@link self::HTML}, or {@link self::ANY}
	 *
	 * @return mixed
	 *
	 * @throws UnexpectedContentType
	 * @throws LycheeInvalidArgumentException
	 */
	public function handle(Request $request, \Closure $next, string $content_type): mixed
	{
		// Skip $except
		if ($this->inExceptArray($request)) {
			return $next($request);
		}

		if ($content_type === self::JSON) {
			if (!$request->expectsJson()) {
				throw new UnexpectedContentType(self::JSON);
			}
		} elseif ($content_type === self::HTML) {
			if (!$request->acceptsHtml()) {
				throw new UnexpectedContentType(self::HTML);
			}
		} elseif ($content_type === self::ANY) {
			// Don't call `$request->acceptsAnyContentType`. It is broken.
			$acceptable = $request->getAcceptableContentTypes();
			if (
				count($acceptable) !== 0 &&
				!in_array('*', $acceptable, true) &&
				!in_array('*/*', $acceptable, true)
			) {
				throw new UnexpectedContentType(self::ANY);
			}
		} else {
			throw new LycheeInvalidArgumentException('$contentType must either be "' . self::JSON . '", "' . self::HTML . '" or "' . self::ANY . '"');
		}

		return $next($request);
	}

	/**
	 * Determine if the request has a URI that should pass through CSRF verification.
	 *
	 * @param \Illuminate\Http\Request $request
	 *
	 * @return bool
	 */
	protected function inExceptArray($request)
	{
		foreach ($this->except as $except) {
			if ($except !== '/') {
				$except = trim($except, '/');
			}

			if ($request->fullUrlIs($except) || $request->is($except)) {
				return true;
			}
		}

		return false;
	}
}
