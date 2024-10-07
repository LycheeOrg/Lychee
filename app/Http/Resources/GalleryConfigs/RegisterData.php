<?php

namespace App\Http\Resources\GalleryConfigs;

use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript()]
class RegisterData extends Data
{
	public function __construct(public bool $success)
	{
	}
}
