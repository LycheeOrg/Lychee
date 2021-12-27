<?php

namespace App\Exceptions\Handlers;

use App\Redirections\ToInstall;
use Illuminate\Database\QueryException as QueryException;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface as HttpException;
use Throwable;

/**
 * Class NoEncryptionKey.
 *
 * If access to the DB is denied, we need to run the installation.
 */
class AccessDBDenied
{
	/**
	 * Render an exception into an HTTP response.
	 *
	 * @param HttpException $e the exception to render to the client
	 *
	 * @return bool true, if this class wants to handle the exception specially
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
	 * @param SymfonyResponse $defaultResponse the default response as it
	 *                                         would be rendered by
	 *                                         {@link \Illuminate\Foundation\Exceptions\Handler::renderHttpException()}
	 * @param HttpException   $e               the exception to render to the
	 *                                         client
	 *
	 * @return SymfonyResponse
	 */
	public function go(SymfonyResponse $defaultResponse, HttpException $e): SymfonyResponse
	{
		try {
			$redirectResponse = ToInstall::go();
			$contentType = $defaultResponse->headers->get('Content-Type');
			if (!empty($contentType)) {
				$redirectResponse->headers->set('Content-Type', $contentType);
				$redirectResponse->setContent($defaultResponse->getContent());
			}

			return $redirectResponse;
		} catch (Throwable) {
			return $defaultResponse;
		}
	}
}
