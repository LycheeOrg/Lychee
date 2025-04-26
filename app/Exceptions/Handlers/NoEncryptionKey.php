<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Exceptions\Handlers;

use App\Contracts\Exceptions\Handlers\HttpExceptionHandler;
use App\Http\Redirections\ToInstall;
use Illuminate\Encryption\MissingAppKeyException;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface as HttpException;

/**
 * Class NoEncryptionKey.
 *
 * If no encryption key exists, we need to run the installation.
 */
class NoEncryptionKey implements HttpExceptionHandler
{
	/**
	 * {@inheritDoc}
	 */
	public function check(HttpException $e): bool
	{
		do {
			if ($e instanceof MissingAppKeyException) {
				return true;
			}
			$e = $e->getPrevious();
		} while ($e !== null);

		return false;
	}

	/**
	 * {@inheritDoc}
	 */
	public function renderHttpException(SymfonyResponse $default_response, HttpException $e): SymfonyResponse
	{
		try {
			$redirect_response = ToInstall::go();
			$content_type = $default_response->headers->get('Content-Type');
			if ($content_type !== null && $content_type !== '') {
				$redirect_response->headers->set('Content-Type', $content_type);
				$content = $default_response->getContent();
				$redirect_response->setContent($content !== false ? $content : null);
			}

			return $redirect_response;
		} catch (\Throwable) {
			return $default_response;
		}
	}
}