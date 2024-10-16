<?php

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

	public function __construct(Configs $c)
	{
		$this->key = $c->key;
		$this->type = ConfigType::tryFrom($c->type_range) ?? $c->type_range;
		$this->value = $c->value;
		$this->documentation = $c->description;
	}

	public static function fromModel(Configs $c): ConfigResource
	{
		return new self($c);
	}
}