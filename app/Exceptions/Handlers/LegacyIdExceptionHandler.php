<?php

namespace App\Exceptions\Handlers;

use App\Contracts\Exceptions\Handlers\HttpExceptionHandler;
use App\Exceptions\ModelDBException;
use Illuminate\Database\QueryException;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface as HttpException;

/**
 * Class AccessDBDenied.
 *
 * If access to the DB is denied, we need to run the installation.
 */
class LegacyIdExceptionHandler implements HttpExceptionHandler
{
	/**
	 * {@inheritDoc}
	 */
	public function check(HttpException $e): bool
	{
		do {
			if (
				($e instanceof QueryException || $e instanceof ModelDBException) &&
				str_contains($e->getMessage(), 'Numeric value out of range: 1264')
			) {
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
		return response()->view('error.error', [
			'code' => $e->getStatusCode(),
			'type' => class_basename($e),
			'message' => 'SQLSTATE: Numeric value out of range: 1264 for column \'legacy_id\'. To fix, please set <pre>force_32bit_ids</pre> to <pre>1</pre> in your config.',
		], $e->getStatusCode(), $e->getHeaders());
	}
}
