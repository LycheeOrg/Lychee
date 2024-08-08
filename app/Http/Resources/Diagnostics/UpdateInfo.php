<?php

namespace App\Http\Resources\Diagnostics;

use App\Enum\VersionChannelType;
use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript()]
class UpdateInfo extends Data
{
	public function __construct(
		public string $info,
		public string $extra,
		public VersionChannelType $channelName,
	) {
	}
}
