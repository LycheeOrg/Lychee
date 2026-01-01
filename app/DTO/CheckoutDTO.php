<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\DTO;

final readonly class CheckoutDTO
{
	public function __construct(
		public bool $is_success,
		public bool $is_redirect = false, // Whether a redirect is needed
		public ?string $redirect_url = null, // URL to redirect to if successful
		public string $message = '', // Error message if not successful
	) {
	}
}
