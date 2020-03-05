<?php

namespace App\Exceptions\Handlers;

use App\Redirections\ToInstall;
use Exception;
use Illuminate\Database\QueryException as QueryException;

class AccessDBDenied
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
		// encryption key does not exist, we need to run the installation
		return $exception instanceof QueryException && (strpos($exception->getMessage(), 'Access denied') !== false);
	}

	/**
	 * @return \Illuminate\Http\Response
	 */
	public function go()
	{
		return ToInstall::go();
	}
}