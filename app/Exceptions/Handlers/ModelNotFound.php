<?php

namespace App\Exceptions\Handlers;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Throwable;

class ModelNotFound
{
	/**
	 * Render an exception into an HTTP response.
	 *
	 * @param Request   $request
	 * @param Throwable $exception
	 *
	 * @return bool
	 */
	public function check($request, Throwable $exception)
	{
		return $exception instanceof ModelNotFoundException;
	}

	/**
	 * @return Response
	 */
	// @codeCoverageIgnoreStart
	public function go()
	{
		return response()->json('false', 200);
	}

	// @codeCoverageIgnoreEnd
}
