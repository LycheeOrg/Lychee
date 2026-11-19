<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Exceptions;

use Symfony\Component\HttpFoundation\Response;

/**
 * WebAuthnVerificationException.
 *
 * A WebAuthn registration or login could not be verified (Feature 107):
 * missing or expired challenge, unknown credential, or a check of the
 * WebAuthn ceremony failed.
 *
 * Returns status code 422 (Unprocessable entity) to an HTTP client.
 */
class WebAuthnVerificationException extends BaseLycheeException
{
	public function __construct(string $msg, ?\Throwable $previous = null)
	{
		parent::__construct(Response::HTTP_UNPROCESSABLE_ENTITY, $msg, $previous);
	}
}
