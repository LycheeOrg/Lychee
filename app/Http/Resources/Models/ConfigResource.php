<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
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
	public bool $is_set;
	public string $documentation;
	public string $details;
	public bool $is_expert;
	public bool $require_se;
	public int|null $order;

	/** @var array<int,string> */
	public array $required_keys;

	public function __construct(Configs $c)
	{
		$this->key = $c->key;
		$this->type = ConfigType::tryFrom($c->type_range) ?? $c->type_range;
		// Values of `password` configs are bcrypt hashes and never leave the server.
		$is_password = $c->type_range === ConfigType::PASSWORD->value;
		$this->value = $is_password ? '' : $c->value;
		$this->is_set = $c->value !== '';
		$this->documentation = $c->description;
		$this->details = $c->details ?? '';
		$this->require_se = $c->level > 0;
		$this->is_expert = $c->is_expert;
		$this->order = (config('app.env', 'dev') === 'dev') ? $c->order : null;
		$this->required_keys = $c->required_keys !== null && $c->required_keys !== '' ? explode(',', $c->required_keys) : [];
	}

	public static function fromModel(Configs $c): ConfigResource
	{
		return new self($c);
	}
}