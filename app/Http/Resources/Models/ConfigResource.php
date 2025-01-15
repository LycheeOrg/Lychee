<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Http\Resources\Models;

use App\Enum\ConfigType;
use App\Models\Configs;
use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript()]
class ConfigResource extends Data
{
	public string $key;
	public ConfigType|string $type;
	public string $value;
	public string $documentation;
	public string $details;
	public bool $require_se;

	public function __construct(Configs $c)
	{
		$this->key = $c->key;
		$this->type = ConfigType::tryFrom($c->type_range) ?? $c->type_range;
		$this->value = $c->value;
		$this->documentation = $c->description;
		$this->details = $c->details;
		$this->require_se = $c->level > 0;
	}

	public static function fromModel(Configs $c): ConfigResource
	{
		return new self($c);
	}
}