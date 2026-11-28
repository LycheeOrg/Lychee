<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Shrinks the security headers of API responses that are not HTML pages.
 *
 * Content-Security-Policy and Permissions-Policy only apply to documents,
 * yet together they weigh over 1 KB. JSON, empty and download responses get
 * a CSP that loads nothing (keeping the configured frame-ancestors) and no
 * Permissions-Policy, so that a remember-me login stays well within a
 * stock nginx `proxy_buffer_size` of 4 KB.
 *
 * The global {@see \Bepsvpt\SecureHeaders\SecureHeadersMiddleware} reads the
 * `secure-headers` config once the route pipeline has returned, so this
 * middleware adjusts the config on the way out.
 */
class ApiSecureHeaders
{
	/**
	 * @param Request  $request the incoming request to serve
	 * @param \Closure $next    the next operation to be applied to the request
	 */
	public function handle(Request $request, \Closure $next): mixed
	{
		$response = $next($request);

		if (!self::isHtmlPage($response)) {
			config([
				'secure-headers.csp' => self::minimalCsp(),
				'secure-headers.permissions-policy.enable' => false,
			]);
		}

		return $response;
	}

	/**
	 * An API URL opened in a browser tab renders its errors as an HTML page:
	 * those keep the page policies.
	 *
	 * A controller returning nothing yields an empty text/html response,
	 * which {@see FixStatusCode} only turns into a 204 later on: no page.
	 */
	private static function isHtmlPage(mixed $response): bool
	{
		return $response instanceof Response &&
			!in_array($response->getContent(), ['', false], true) &&
			str_starts_with(strtolower($response->headers->get('Content-Type') ?? ''), 'text/html');
	}

	/**
	 * @return array<string,mixed>
	 */
	private static function minimalCsp(): array
	{
		return [
			'enable' => config('secure-headers.csp.enable', true),
			'default-src' => ['none' => true],
			'frame-ancestors' => config('secure-headers.csp.frame-ancestors', ['none' => true]),
		];
	}
}
