<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;

class Handler extends ExceptionHandler
{
	/**
	 * A list of the exception types that are not reported.
	 *
	 * @var array
	 */
	protected $dontReport = [
		DecryptException::class,
	];

	/**
	 * A list of the inputs that are never flashed for validation exceptions.
	 *
	 * @var array
	 */
	protected $dontFlash = [
		'password',
		'password_confirmation',
	];

	/**
	 * Report or log an exception.
	 *
	 * @param Exception $exception
	 *
	 * @return void
	 *
	 * @throws Exception
	 */
	public function report(Exception $exception)
	{
		parent::report($exception);
	}

	/**
	 * Render an exception into an HTTP response.
	 *
	 * @param \Illuminate\Http\Request $request
	 * @param Exception                $exception
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function render($request, Exception $exception)
	{
		if ($exception instanceof DecryptException && $exception->getMessage() === 'The payload is invalid.') {
			return response()->json(['error' => 'Session timed out'], 400);
		}

		return parent::render($request, $exception);
	}
}
