<?php

namespace App\Http\Resources;

use App\Enum\OauthProvidersType;
use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript()]
class OauthData extends Data
{
	public function __construct(
		public OauthProvidersType $providerType,
		public string $registrationRoute,
	) {
	}
}