<?php

namespace App\Exceptions\Handlers;

use Illuminate\Http\Exceptions\PostTooLargeException;
use Illuminate\Http\Response;
use Throwable;

class RequestTooLarge
{
	/**
	 * Render an exception into an HTTP response.
	 *
	 * @param Illuminate\Http\Request $request
	 * @param Throwable               $exception
	 *
	 * @return bool
	 */
	public function check($request, Throwable $exception)
	{
		return $exception instanceof PostTooLargeException;
	}

	/**
	 * @return \Illuminate\Http\Response
	 */
	// @codeCoverageIgnoreStart
	public function go()
	{
		return response()->json('Error: Request too large. Consider increasing the PHP post_max_size limit.');
	}

	// @codeCoverageIgnoreEnd
}