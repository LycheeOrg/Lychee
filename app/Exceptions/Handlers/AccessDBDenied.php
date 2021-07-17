<?php

namespace App\Exceptions\Handlers;

use App\Redirections\ToInstall;
use Illuminate\Database\QueryException as QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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
	 * @return RedirectResponse
	 */
	// @codeCoverageIgnoreStart
	public function go(): RedirectResponse
	{
		return ToInstall::go();
	}

	// @codeCoverageIgnoreEnd
}
