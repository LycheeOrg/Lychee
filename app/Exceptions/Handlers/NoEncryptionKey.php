<?php

namespace App\Exceptions\Handlers;

use App\Redirections\ToInstall;
use Illuminate\Http\Response;
use RuntimeException;
use Throwable;

class NoEncryptionKey
{
	/**
	 * Render an exception into an HTTP response.
	 *
	 * @param Illuminate\Http\Request $request
	 * @param Throwable               $exception
	 *
	 * @return Response
	 */
	public function check($request, Throwable $exception)
	{
		// encryption key does not exist, we need to run the installation
		return $exception instanceof RuntimeException && $exception->getMessage() === 'No application encryption key has been specified.';
	}

	/**
	 * @return Response
	 */
	// @codeCoverageIgnoreStart
	public function go()
	{
		touch(base_path('.NO_SECURE_KEY'));

		return ToInstall::go();
	}

	// @codeCoverageIgnoreEnd
}