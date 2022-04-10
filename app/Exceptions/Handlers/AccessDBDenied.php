<?php

namespace App\Exceptions\Handlers;

use App\Redirections\ToInstall;
use Illuminate\Database\QueryException;
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
		return $exception instanceof QueryException && (str_contains($exception->getMessage(), 'Access denied'));
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
