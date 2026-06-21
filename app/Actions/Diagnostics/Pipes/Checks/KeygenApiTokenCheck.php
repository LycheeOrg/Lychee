<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Actions\Diagnostics\Pipes\Checks;

use App\Contracts\DiagnosticPipe;
use App\DTO\DiagnosticData;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use LycheeVerify\TokenExtension;

/**
 * Check the expiration status of the Keygen API token.
 * Only displayed if the user is an admin and a token is configured.
 */
class KeygenApiTokenCheck implements DiagnosticPipe
{
	public function __construct(
		private TokenExtension $token_extension,
	) {
	}

	/**
	 * {@inheritDoc}
	 */
	public function handle(array &$data, \Closure $next): array
	{
		/** @var User|null */
		$user = Auth::user();

		if ($user?->may_administrate !== true) {
			return $next($data);
		}

		/** @var string $api_key */
		$api_key = config('verify.keygen_api_key', '');
		if ($api_key === '') {
			return $next($data);
		}

		// @codeCoverageIgnoreStart
		$result = $this->token_extension->extend();

		if (!$result->success) {
			$data[] = DiagnosticData::error(
				'Keygen API token error: ' . ($result->message ?? 'unknown error') . ' Go to keygen.lycheeorg.dev to retrieve a new one.',
				self::class,
			);

			return $next($data);
		}

		if ($result->expires_at !== null && $result->expires_at->isBefore(now()->addWeek())) {
			$data[] = DiagnosticData::warn(
				'Your Keygen API token expires on ' . $result->expires_at->toDateString() . '. Consider renewing it at keygen.lycheeorg.dev.',
				self::class,
			);
		}
		// @codeCoverageIgnoreEnd

		return $next($data);
	}
}
