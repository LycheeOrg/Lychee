<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Http\Resources\Shop;

use Illuminate\Http\Request;
use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;
use Symfony\Component\HttpFoundation\Response;

#[TypeScript()]
class CheckoutResource extends Data
{
	public function __construct(
		public readonly bool $is_success,
		public readonly bool $is_redirect = false,
		public readonly ?string $redirect_url = null,
		public readonly ?string $complete_url = null,
		public readonly string $message = '',
		public readonly ?OrderResource $order = null,
	) {
	}

	protected function calculateResponseStatus(Request $request): int
	{
		return $this->is_success ? Response::HTTP_OK : Response::HTTP_BAD_REQUEST;
	}
}