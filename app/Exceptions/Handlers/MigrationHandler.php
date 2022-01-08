<?php

namespace App\Exceptions\Handlers;

use App\Contracts\HttpExceptionHandler;
use App\Exceptions\MigrationAlreadyCompletedException;
use App\Exceptions\MigrationRequiredException;
use App\Redirections\ToHome;
use App\Redirections\ToMigration;
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
	public function renderHttpException(SymfonyResponse $defaultResponse, HttpException $e): SymfonyResponse
	{
		try {
			$redirectResponse = $this->toMigration ? ToMigration::go() : ToHome::go();
			$contentType = $defaultResponse->headers->get('Content-Type');
			if (!empty($contentType)) {
				$redirectResponse->headers->set('Content-Type', $contentType);
				$redirectResponse->setContent($defaultResponse->getContent());
			}

			return $redirectResponse;
		} catch (\Throwable) {
			return $defaultResponse;
		}
	}
}
