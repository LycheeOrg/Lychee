<?php

namespace App\Exceptions\Handlers;

use Exception;
use RuntimeException;
use App\Redirections\ToInstall;

class NoEncryptionKey {

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
        return ($exception instanceof RuntimeException && $exception->getMessage() === 'No application encryption key has been specified.');
    }

    /**
     * @return \Illuminate\Http\Response
     */
    public function go()
    {
        return ToInstall::go();
    }

}