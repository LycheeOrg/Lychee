<?php

namespace App\Exceptions;

use App\Exceptions\Handlers\AccessDBDenied;
use App\Exceptions\Handlers\ApplyComposer;
use App\Exceptions\Handlers\InvalidPayload;
use App\Exceptions\Handlers\NoEncryptionKey;
use Exception;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;

class Handler extends ExceptionHandler
{
	protected $redirectTo = 'home';

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
	 * @param Throwable $exception
	 *
	 * @return void
	 *
	 * @throws Throwable
	 */
	public function report(Throwable $exception)
	{
		// @codeCoverageIgnoreStart
		parent::report($exception);
		// @codeCoverageIgnoreEnd
	}

	/**
	 * Render an exception into an HTTP response.
	 *
	 * @param \Illuminate\Http\Request $request
	 * @param Throwable                $exception
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function render($request, Throwable $exception)
	{
		$checks = [];
		$checks[] = new NoEncryptionKey();
		$checks[] = new InvalidPayload();
		$checks[] = new AccessDBDenied();
		$checks[] = new ApplyComposer();

		foreach ($checks as $check) {
			if ($check->check($request, $exception)) {
				// @codeCoverageIgnoreStart
				return $check->go();
				// @codeCoverageIgnoreEnd
			}
		}

		// @codeCoverageIgnoreStart
		return parent::render($request, $exception);
		// @codeCoverageIgnoreEnd
	}
}
