<?php

namespace App\Exceptions\Handlers;

use App\Redirections\ToInstall;
use Exception;
use Illuminate\Http\Response;
use Illuminate\View\View;
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
	 * @return bool
	 */
	public function check($request, Throwable $exception)
	{
		// encryption key does not exist, we need to run the installation
		return $exception instanceof RuntimeException && $exception->getMessage() === 'No application encryption key has been specified.';
	}

	/**
	 * @return Response or View
	 */
	// @codeCoverageIgnoreStart
	public function go()
	{
		try {
			touch(base_path('.NO_SECURE_KEY'));

			return ToInstall::go();
		} catch (Exception $e) {
			return response()->view('error.error', ['code' => '500', 'message' => 'WRITE ACCESS REQUIRED on ' . base_path() . '<br>in order to create <code>.NO_SECURE_KEY</code>, <code>.env</code>, <code>installed.log</code> files']);
		}
	}

	// @codeCoverageIgnoreEnd
}