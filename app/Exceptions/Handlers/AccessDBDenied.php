<?php

namespace App\Exceptions\Handlers;

use App\Redirections\ToInstall;
use Illuminate\Database\QueryException as QueryException;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Throwable;

class AccessDBDenied
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
		// encryption key does not exist, we need to run the installation
		return $exception instanceof QueryException && (strpos($exception->getMessage(), 'Access denied') !== false);
	}

	/**
	 * @return Request
	 */
	// @codeCoverageIgnoreStart
	public function go(): Request
	{
		return ToInstall::go();
	}

	// @codeCoverageIgnoreEnd
}
