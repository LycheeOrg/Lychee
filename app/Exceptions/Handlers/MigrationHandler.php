<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Exceptions\Handlers;

use App\Contracts\Exceptions\Handlers\HttpExceptionHandler;
use App\Exceptions\MigrationAlreadyCompletedException;
use App\Exceptions\MigrationRequiredException;
use App\Http\Redirections\ToHome;
use App\Http\Redirections\ToMigration;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface as HttpException;

/**
 * Class MigrationHandler.
 *
 * If the exception {@link MigrationRequiredException} or
 * {@link MigrationAlreadyCompletedException} is thrown, this handler
 * redirects to the migration or home page.
 *
 * Also see {@link InstallationHandler}.
 */
class MigrationHandler implements HttpExceptionHandler
{
	protected bool $toMigration;

	/**
	 * {@inheritDoc}
	 */
	public function check(HttpException $e): bool
	{
		do {
			if ($e instanceof MigrationRequiredException) {
				$this->toMigration = true;

				return true;
			}
			if ($e instanceof MigrationAlreadyCompletedException) {
				$this->toMigration = false;

				return true;
			}
		} while ($e = $e->getPrevious());

		return false;
	}

	/**
	 * {@inheritDoc}
	 */
	public function renderHttpException(SymfonyResponse $default_response, HttpException $e): SymfonyResponse
	{
		try {
			$redirect_response = $this->toMigration ? ToMigration::go() : ToHome::go();
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
