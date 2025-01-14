<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Ensures that responses with empty content return status code 204.
 *
 * This fixes a bug in Laravel.
 */
class FixStatusCode
{
	/**
	 * Handle an incoming request.
	 *
	 * @param Request  $request
	 * @param \Closure $next
	 *
	 * @return Response
	 *
	 * @throws \InvalidArgumentException
	 */
	public function handle(Request $request, \Closure $next): Response
	{
		/** @var Response $response */
		$response = $next($request);

		$content = $response->getContent();
		// Note: The content is always empty for binary file or streamed
		// responses at this stage, because their content is sent
		// asynchronously.
		// Hence, we must not overwrite the status code with 204 for those
		// kinds of responses.
		if (
			($content === false || $content === '') &&
			!($response instanceof BinaryFileResponse) &&
			!($response instanceof StreamedResponse)
		) {
			$response->setStatusCode(Response::HTTP_NO_CONTENT);
		}

		return $response;
	}
}
