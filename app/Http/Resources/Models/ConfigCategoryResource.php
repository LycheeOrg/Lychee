<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Http\Resources\Models;

use App\Models\ConfigCategory;
use Illuminate\Support\Collection;
use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\LiteralTypeScriptType;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript()]
class ConfigCategoryResource extends Data
{
	public string $cat;
	public string $name;
	public string $description;
	/** @var Collection<int,ConfigResource> */
	#[LiteralTypeScriptType('App.Http.Resources.Models.ConfigResource[]')]
	public Collection $configs;
	public int $priority;

	public function __construct(ConfigCategory $c)
	{
		$this->cat = $c->cat;
		$this->name = $c->name;
		$this->description = $c->description ?? '';
		$this->priority = $c->order;
		/** @disregard P1006 */
		$this->configs = ConfigResource::collect($c->configs);
	}

	public static function fromModel(ConfigCategory $c): ConfigCategoryResource
	{
		return new self($c);
	}
}