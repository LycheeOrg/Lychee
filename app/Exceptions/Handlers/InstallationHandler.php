<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Exceptions\Handlers;

use App\Contracts\Exceptions\Handlers\HttpExceptionHandler;
use App\Exceptions\InstallationAlreadyCompletedException;
use App\Exceptions\InstallationRequiredException;
use App\Http\Redirections\ToInstall;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface as HttpException;

/**
 * Class InstallationHandler.
 *
 * If the exception {@link InstallationRequiredException} or
 * {@link InstallationAlreadyCompletedException} is thrown, this handler
 * redirects to the installation or to the home page.
 *
 * Also see {@link MigrationHandler}.
 */
class InstallationHandler implements HttpExceptionHandler
{
	protected bool $toInstall;

	/**
	 * {@inheritDoc}
	 */
	public function check(HttpException $e): bool
	{
		do {
			if ($e instanceof InstallationRequiredException) {
				$this->toInstall = true;

				return true;
			}
			if ($e instanceof InstallationAlreadyCompletedException) {
				$this->toInstall = false;

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
			if ($this->toInstall) {
				$redirect_response = ToInstall::go();
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