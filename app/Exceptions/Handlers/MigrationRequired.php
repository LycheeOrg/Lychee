<?php

namespace App\Exceptions\Handlers;

use App\Contracts\HttpExceptionHandler;
use App\Exceptions\MigrationRequiredException;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface as HttpException;

/**
 * Class MigrationRequired.
 *
 * If the exception {@link MigrationRequiredException} is thrown, we
 * present the migration page.
 *
 * This class is asymmetric to {@link InstallationRequired}.
 * {@link InstallationRequired} only redirects to the installation pages,
 * but does not present them directly.
 * This class directly return the migration page, but does not redirect there.
 * As migration is also the last step of installation, we may be should also
 * use a redirection here for more consistency.
 *
 * TODO: Re-consider a redirection instead of directly delivering the migration page.
 */
class MigrationRequired implements HttpExceptionHandler
{
	/**
	 * {@inheritDoc}
	 */
	public function check(HttpException $e): bool
	{
		do {
			if ($e instanceof MigrationRequiredException) {
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
			return response()->view('error.update', [
				'code' => $e->getCode(),
				'message' => $e->getMessage(),
			]);
		} catch (\Throwable) {
			return $defaultResponse;
		}
	}
}
