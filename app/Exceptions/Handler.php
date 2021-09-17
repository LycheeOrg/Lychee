<?php

namespace App\Exceptions;

use App\Exceptions\Handlers\AccessDBDenied;
use App\Exceptions\Handlers\ApplyComposer;
use App\Exceptions\Handlers\InvalidPayload;
use App\Exceptions\Handlers\NoEncryptionKey;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Request as IlluminateRequest;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use Throwable;

class Handler extends ExceptionHandler
{
	/**
	 * Render an exception into an HTTP response.
	 *
	 * @param IlluminateRequest $request
	 * @param Throwable         $e
	 *
	 * @return SymfonyResponse
	 *
	 * @throws Throwable
	 */
	public function render($request, Throwable $e): SymfonyResponse
	{
		$checks = [];
		$checks[] = new NoEncryptionKey();
		$checks[] = new InvalidPayload();
		$checks[] = new AccessDBDenied();
		$checks[] = new ApplyComposer();

		foreach ($checks as $check) {
			if ($check->check($request, $e)) {
				return $check->go();
			}
		}

		return parent::render($request, $e);
	}
}
