<?php

namespace App\Exceptions\Handlers;

use App\Contracts\Exceptions\Handlers\HttpExceptionHandler;
use App\Http\Redirections\ToInstall;
use Illuminate\Encryption\MissingAppKeyException;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface as HttpException;

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
			if ($contentType !== null && $contentType !== '') {
				$redirectResponse->headers->set('Content-Type', $contentType);
				$content = $defaultResponse->getContent();
				$redirectResponse->setContent($content !== false ? $content : null);
			}

			return $redirectResponse;
		} catch (\Throwable) {
			return $defaultResponse;
		}
	}
}
