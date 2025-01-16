<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Exceptions\Handlers;

use App\Contracts\Exceptions\Handlers\HttpExceptionHandler;
use App\Exceptions\AdminUserAlreadySetException;
use App\Exceptions\AdminUserRequiredException;
use App\Http\Redirections\ToAdminSetter;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface as HttpException;

/**
 * Class AdminSetterHandler.
 *
 * If the exception {@link AdminUserRequiredException} or
 * {@link AdminUserAlreadySetException} is thrown, this handler
 * redirects to the admin set up page or to the home page.
 *
 * Also see {@link MigrationHandler}.
 */
class AdminSetterHandler implements HttpExceptionHandler
{
	protected bool $toAdminSetter;

	/**
	 * {@inheritDoc}
	 */
	public function check(HttpException $e): bool
	{
		do {
			if ($e instanceof AdminUserRequiredException) {
				$this->toAdminSetter = true;

				return true;
			}
			if ($e instanceof AdminUserAlreadySetException) {
				$this->toAdminSetter = false;

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
			if ($this->toAdminSetter) {
				$redirectResponse = ToAdminSetter::go();
				$contentType = $defaultResponse->headers->get('Content-Type');
				if ($contentType !== null && $contentType !== '') {
					$redirectResponse->headers->set('Content-Type', $contentType);
					$content = $defaultResponse->getContent();
					$redirectResponse->setContent($content !== false ? $content : null);
				}

				return $redirectResponse;
			} else {
				return $defaultResponse;
			}
		} catch (\Throwable) {
			return $defaultResponse;
		}
	}
}
