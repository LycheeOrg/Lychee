<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Exceptions\Handlers;

use App\Contracts\Exceptions\Handlers\HttpExceptionHandler;
use Illuminate\Foundation\ViteManifestNotFoundException;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface as HttpException;

/**
 * Class ViteManifestNotFoundHandler.
 *
 * If vite manifest is not found, indicate user must run "npm run dev" rather than a big error message.
 */
class ViteManifestNotFoundHandler implements HttpExceptionHandler
{
	/**
	 * {@inheritDoc}
	 */
	public function check(HttpException $e): bool
	{
		do {
			if ($e instanceof ViteManifestNotFoundException) {
				return true;
			}
		} while ($e = $e->getPrevious());

		return false;
	}

	/**
	 * {@inheritDoc}
	 */
	public function renderHttpException(SymfonyResponse $defaultResponse, HttpException $e): SymfonyResponse
	{
		return response()->view('error.error', [
			'code' => $e->getStatusCode(),
			'type' => class_basename($e),
			'message' => 'Vite manifest not found, please execute `npm run dev`',
		], $e->getStatusCode(), $e->getHeaders());
	}
}
