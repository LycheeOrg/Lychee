<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Exceptions\Handlers;

use App\Contracts\Exceptions\Handlers\HttpExceptionHandler;
use App\Exceptions\ModelDBException;
use Illuminate\Database\QueryException;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface as HttpException;

/**
 * Class LegacyIdExceptionHandler.
 *
 * If SQLSTATE: Numeric value out of range: 1264 is throw it means we are interacting with 32 bit DB.
 * Advise to set config parameter.
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
			'message' => 'SQLSTATE: Numeric value out of range: 1264 for column \'legacy_id\'. To fix, please set `force_32bit_ids` to `1` in your Settings => More.',
		], $e->getStatusCode(), $e->getHeaders());
	}
}
