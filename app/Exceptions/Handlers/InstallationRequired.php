<?php

namespace App\Exceptions\Handlers;

use App\Contracts\HttpExceptionHandler;
use App\Exceptions\InstallationRequiredException;
use App\Redirections\ToInstall;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface as HttpException;

/**
 * Class InstallationRequired.
 *
 * If the exception {@link InstallationRequiredException} is thrown, we need
 * to run the installation.
 */
class InstallationRequired implements HttpExceptionHandler
{
	/**
	 * {@inheritDoc}
	 */
	public function check(HttpException $e): bool
	{
		do {
			if ($e instanceof InstallationRequiredException) {
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
