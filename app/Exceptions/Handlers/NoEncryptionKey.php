<?php

namespace App\Exceptions\Handlers;

use App\Contracts\HttpExceptionHandler;
use App\Redirections\ToInstall;
use Illuminate\Encryption\MissingAppKeyException;
use function Safe\touch;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface as HttpException;
use Throwable;

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
			if ($e->getMessage() === 'No application encryption key has been specified.') {
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
			try {
				touch(base_path('.NO_SECURE_KEY'));
			} catch (Throwable) {
				// do nothing
			}
			$redirectResponse = ToInstall::go();
			$contentType = $defaultResponse->headers->get('Content-Type');
			if ($contentType !== null && $contentType !== '') {
				$redirectResponse->headers->set('Content-Type', $contentType);
				$content = $defaultResponse->getContent();
				$redirectResponse->setContent($content !== false ? $content : null);
			}

			return $redirectResponse;
		} catch (Throwable) {
			return $defaultResponse;
		}
	}
}
