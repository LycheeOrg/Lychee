<?php

namespace App\Exceptions\Handlers;

use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;
use Throwable;

class FileTooLarge
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
		return $exception instanceof ValidationException;
	}

	/**
	 * @return \Illuminate\Http\Response
	 */
	// @codeCoverageIgnoreStart
	public function go()
	{
		return response()->json('Error: Request invalid. If this happens during photo upload, consider increasing the PHP upload_max_filesize limit.');
	}

	// @codeCoverageIgnoreEnd
}