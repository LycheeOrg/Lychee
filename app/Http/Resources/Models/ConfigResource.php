<?php

namespace App\Http\Resources\Models;

use App\Enum\ConfigType;
use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript()]
class ConfigResource extends Data
{
	public string $key;
	public ConfigType|string $type;
	public string $value;
	public string $documentation;

	public function __construct(string $key, ConfigType|string $type, string $value, string $doc)
	{
		$this->key = $key;
		$this->type = $type;
		$this->value = $value;
		$this->documentation = $doc;
	}
}