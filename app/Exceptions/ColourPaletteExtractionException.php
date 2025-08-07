<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Exceptions;

use Symfony\Component\HttpFoundation\Response;

/**
 * ColourPaletteExtractionException.
 *
 * This exception is thrown, if the colour palette extraction fails.
 * It returns status code 500 (Internal Server Error) to the HTTP client.
 */
class ColourPaletteExtractionException extends BaseLycheeException
{
	public const DEFAULT_MESSAGE = 'Colour palette extraction failed.';

	public function __construct(string $msg = self::DEFAULT_MESSAGE, ?\Throwable $previous = null)
	{
		parent::__construct(Response::HTTP_INTERNAL_SERVER_ERROR, $msg, $previous);
	}
}
