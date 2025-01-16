<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Exceptions\Handlers;

use App\Contracts\Exceptions\Handlers\HttpExceptionHandler;
use App\Http\Redirections\ToInstall;
use Illuminate\Database\QueryException;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface as HttpException;

/**
 * Class AccessDBDenied.
 *
 * If access to the DB is denied, we need to run the installation.
 */
class AccessDBDenied implements HttpExceptionHandler
{
	/**
	 * {@inheritDoc}
	 */
	public function check(HttpException $e): bool
	{
		do {
			if ($e instanceof QueryException && str_contains($e->getMessage(), 'Access denied')) {
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
		try {
			$redirectResponse = ToInstall::go();
			$contentType = $defaultResponse->headers->get('Content-Type');
			if ($contentType !== null && $contentType !== '') {
				$redirectResponse->headers->set('Content-Type', $contentType);
				$content = $defaultResponse->getContent();
				$redirectResponse->setContent($content !== false ? $content : null);
			}

			return $redirectResponse;
		} catch (\Throwable) {
			return $defaultResponse;
		}
	}
}
