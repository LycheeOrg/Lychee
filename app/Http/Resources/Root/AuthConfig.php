<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Http\Resources\Root;

use App\Enum\OauthProvidersType;
use Laragear\WebAuthn\Models\WebAuthnCredential;
use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript()]
class AuthConfig extends Data
{
	/** @var array<int,OauthProvidersType> */
	public readonly array $oauthProviders;
	public readonly bool $u2f_enabled;

	public function __construct()
	{
		$providers = [];
		foreach (OauthProvidersType::cases() as $oauth) {
			$client_id = config('services.' . $oauth->value . '.client_id');
			if ($client_id === null || $client_id === '') {
				continue;
			}
			$providers[] = $oauth;
		}
		$this->oauthProviders = $providers;
		$this->u2f_enabled = WebAuthnCredential::query()->whereNull('disabled_at')->count() > 0;
	}
}