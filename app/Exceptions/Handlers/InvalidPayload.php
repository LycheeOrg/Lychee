<?php

namespace App\Exceptions\Handlers;

use Exception;
use Illuminate\Contracts\Encryption\DecryptException;

class InvalidPayload
{
	/**
	 * Render an exception into an HTTP response.
	 *
	 * @param \Illuminate\Http\Request $request
	 * @param Exception                $exception
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function check($request, Exception $exception)
	{
		return $exception instanceof DecryptException && $exception->getMessage() === 'The payload is invalid.';
	}

	/**
	 * @return \Illuminate\Http\Response
	 */
	public function go()
	{
		return response()->json(['error' => 'Session timed out'], 400);
	}
}