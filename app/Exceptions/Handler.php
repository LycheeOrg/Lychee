<?php

namespace App\Exceptions;

use App\Contracts\HttpExceptionHandler;
use App\Exceptions\Handlers\AccessDBDenied;
use App\Exceptions\Handlers\InstallationRequired;
use App\Exceptions\Handlers\MigrationRequired;
use App\Exceptions\Handlers\NoEncryptionKey;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Support\Arr;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

class Handler extends ExceptionHandler
{
	/**
	 * Renders the given HttpException.
	 *
	 * This method is called by the framework if
	 *  1. `config('app.debug')` is not set, i.e. the application is not in debug mode
	 *  2. the client expects an HTML response
	 *
	 * @param HttpExceptionInterface $e
	 *
	 * @return SymfonyResponse
	 *
	 * @noinspection PhpDocMissingThrowsInspection
	 * @noinspection PhpUnhandledExceptionInspection
	 */
	protected function renderHttpException(HttpExceptionInterface $e): SymfonyResponse
	{
		// If we are in debug mode, we use the internal method of the parent
		// method to render a useful response with backtrace, etc., depending
		// on the available extensions (i.e. Whoops, Symfonfy renderer, etc.)
		// If we are in non-debug mode, we render our own template that
		// matches Lychee's style and only contains rudimentary information.
		$defaultResponse = config('app.debug') ?
			$this->convertExceptionToResponse($e) :
			response()->view('error.error', [
				'code' => $e->getStatusCode(),
				'type' => class_basename($e),
				'message' => $e->getMessage(),
			], $e->getStatusCode(), $e->getHeaders());

		// We check, if any of our special handlers wants to do something.

		/** @var HttpExceptionHandler[] $checks */
		$checks = [
			new NoEncryptionKey(),
			new AccessDBDenied(),
			new InstallationRequired(),
			new MigrationRequired(),
		];

		foreach ($checks as $check) {
			if ($check->check($e)) {
				return $check->renderHttpException($defaultResponse, $e);
			}
		}

		return $defaultResponse;
	}

	/**
	 * Converts the given exception to an array.
	 *
	 * The result only includes details about the exception, if the
	 * application is in debug mode.
	 * Identical to
	 * {@link \Illuminate\Foundation\Exceptions\Handler::convertExceptionToAray()}
	 * but recursively adds the previous exceptions, too.
	 *
	 * @param \Throwable $e
	 *
	 * @return array
	 */
	protected function convertExceptionToArray(\Throwable $e): array
	{
		try {
			return config('app.debug') ? [
				'message' => $e->getMessage(),
				'exception' => get_class($e),
				'file' => $e->getFile(),
				'line' => $e->getLine(),
				'trace' => collect($e->getTrace())->map(function ($trace) {
					return Arr::except($trace, ['args']);
				})->all(),
				'previous_exception' => $e->getPrevious() ? $this->convertExceptionToArray($e->getPrevious()) : null,
			] : [
				'message' => $this->isHttpException($e) ? $e->getMessage() : 'Server Error',
			];
		} catch (\Throwable) {
			return [];
		}
	}
}
