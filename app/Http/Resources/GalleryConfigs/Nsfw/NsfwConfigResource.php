<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Resources\GalleryConfigs\Nsfw;

use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\LiteralTypeScriptType;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript()]
class NsfwConfigResource extends Data
{
	/**
	 * @param NsfwConfigPresetResource[] $presets
	 */
	public function __construct(
		public NsfwConfigSettingsResource $config,
		#[DataCollectionOf(NsfwConfigPresetResource::class)]
		#[LiteralTypeScriptType('App.Http.Resources.GalleryConfigs.Nsfw.NsfwConfigPresetResource[]')]
		public array $presets = [],
	) {
	}
}
