<?php

namespace App\Exceptions\Handlers;

use ErrorException;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Throwable;

class ApplyComposer
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
		return $exception instanceof ErrorException && (str_contains($exception->getFile(), 'laravel/framework/src/Illuminate/Routing/Router.php'));
	}

	/**
	 * @return Response
	 */
	// @codeCoverageIgnoreStart
	public function go(): Response
	{
		return response()->view('error.error', ['code' => '500', 'message' => 'Missing dependency, please do: <code>composer install --no-dev</code><br>(or use the release channel.)']);
	}
}
