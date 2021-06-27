<?php

namespace App\Exceptions\Handlers;

use App\Models\Photo;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
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
	public function check(Request $request, Throwable $exception)
	{
		return ($exception instanceof ModelNotFoundException) && ($exception->getModel() !== Photo::class);
	}

	/**
	 * @return JsonResponse
	 */
	// @codeCoverageIgnoreStart
	public function go(): JsonResponse
	{
		return response()->json('false', 200);
	}

	// @codeCoverageIgnoreEnd
}
