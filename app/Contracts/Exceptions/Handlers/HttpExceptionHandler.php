<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Contracts\Exceptions\Handlers;

use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface as HttpException;

interface HttpExceptionHandler
{
	/**
	 * Checks whether this handler wants to handle the HTTP exception.
	 *
	 * @param HttpException $e the exception
	 *
	 * @return bool true, if this class wants to handle the exception
	 */
	public function check(HttpException $e): bool;

	/**
	 * Renders the exception for the client.
	 *
	 * Note, this method is called by the framework after the framework
	 * has already determined that the clients expects an HTML response.
	 *
	 * @param SymfonyResponse $defaultResponse the default response as it
	 *                                         would be rendered by
	 *                                         {@link \Illuminate\Foundation\Exceptions\Handler::renderHttpException()}
	 * @param HttpException   $e               the exception to render to the
	 *                                         client
	 *
	 * @return SymfonyResponse
	 */
	public function renderHttpException(SymfonyResponse $defaultResponse, HttpException $e): SymfonyResponse;
}
