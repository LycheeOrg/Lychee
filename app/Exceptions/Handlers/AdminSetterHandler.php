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
			if ($this->toAdminSetter) {
				$redirect_response = ToAdminSetter::go();
				$content_type = $default_response->headers->get('Content-Type');
				if ($content_type !== null && $content_type !== '') {
					$redirect_response->headers->set('Content-Type', $content_type);
					$content = $default_response->getContent();
					$redirect_response->setContent($content !== false ? $content : null);
				}

				return $redirect_response;
			} else {
				return $default_response;
			}
		} catch (\Throwable) {
			return $default_response;
		}
	}
}