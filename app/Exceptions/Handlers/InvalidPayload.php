<?php

namespace App\Exceptions\Handlers;

use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

class InvalidPayload
{
	/**
	 * Render an exception into an HTTP response.
	 *
	 * @param Request   $request
	 * @param Throwable $exception
	 *
	 * @return bool
	 */
	public function check(Request $request, Throwable $exception)
	{
		return $exception instanceof DecryptException;
	}

	/**
	 * @return JsonResponse
	 */
	// @codeCoverageIgnoreStart
	public function go(): JsonResponse
	{
		return response()->json(['error' => 'Session timed out'], 400);
	}

	// @codeCoverageIgnoreEnd
}
