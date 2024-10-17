<?php

namespace App\Http\Resources\Editable;

use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript()]
class EditableConfigResource extends Data
{
	public string $key;
	public ?string $value;

	public function __construct(string $key, ?string $value)
	{
		$this->key = $key;
		$this->value = $value;
	}
}
