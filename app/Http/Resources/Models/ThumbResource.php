<?php

namespace App\Http\Resources\Models;

use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript()]
class ThumbResource extends Data
{
	public string $id;
	public string $type;
	public ?string $thumb;
	public ?string $thumb2x;

	public function __construct(string $id, string $type, string $thumbUrl, ?string $thumb2xUrl = null)
	{
		$this->id = $id;
		$this->type = $type;
		$this->thumb = $thumbUrl;
		$this->thumb2x = $thumb2xUrl;
	}
}
