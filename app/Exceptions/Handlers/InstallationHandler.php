<?php

namespace App\Exceptions\Handlers;

use App\Contracts\HttpExceptionHandler;
use App\Exceptions\InstallationAlreadyCompletedException;
use App\Exceptions\InstallationRequiredException;
use App\Redirections\ToHome;
use App\Redirections\ToInstall;
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
		} while ($e = $e->getPrevious());

		return false;
	}

	/**
	 * {@inheritDoc}
	 */
	public function renderHttpException(SymfonyResponse $defaultResponse, HttpException $e): SymfonyResponse
	{
		try {
			$redirectResponse = $this->toInstall ? ToInstall::go() : ToHome::go();
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
