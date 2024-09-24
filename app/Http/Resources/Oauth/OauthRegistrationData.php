<?php

namespace App\Http\Resources\Oauth;

use App\Enum\OauthProvidersType;
use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript()]
class OauthRegistrationData extends Data
{
	public function __construct(
		public OauthProvidersType $providerType,
		public bool $isEnabled,
		public string $registrationRoute,
	) {
	}
}