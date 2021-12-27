<?php

namespace App\Exceptions\Handlers;

use App\Redirections\ToInstall;
use Illuminate\Encryption\MissingAppKeyException;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface as HttpException;
use Throwable;

/**
 * Class NoEncryptionKey.
 *
 * If no encryption key exists, we need to run the installation.
 */
class NoEncryptionKey
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
			if ($e instanceof MissingAppKeyException) {
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
			touch(base_path('.NO_SECURE_KEY'));
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
