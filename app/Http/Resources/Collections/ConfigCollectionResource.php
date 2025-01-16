<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Http\Resources\Collections;

use App\Http\Resources\Models\ConfigResource;
use App\Models\Configs;
use Illuminate\Support\Collection;
use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

/**
 * Data Transfer Object (DTO) to transmit the configurations.
 */
#[TypeScript()]
class ConfigCollectionResource extends Data
{
	/** @var array<string,ConfigResource[]> */
	public array $configs;

	/**
	 * @param Collection<int,Configs> $configs
	 *
	 * @return void
	 */
	public function __construct(Collection $configs)
	{
		$configs
			// Group by category
			->chunkWhile(fn (Configs $value, int $key, Collection $chunk) => $value->cat === $chunk->last()->cat)
			// For each category, map the configs to ConfigResource
			->each(function (Collection $chunk) {
				$configs_data = ConfigResource::collect($chunk->all());
				$this->configs[$chunk->first()->cat] = $configs_data;
			});
	}
}