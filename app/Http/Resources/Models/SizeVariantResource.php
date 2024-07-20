<?php

namespace App\Http\Resources\Models;

use App\Enum\SizeVariantType;
use App\Models\SizeVariant;
use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript()]
class SizeVariantResource extends Data
{
	public SizeVariantType $type;
	public int $filesize;
	public int $height;
	public int $width;
	public ?string $url;

	public function __construct(SizeVariant $sizeVariant, bool $noUrl = false)
	{
		$this->type = $sizeVariant->type;
		$this->filesize = $sizeVariant->filesize;
		$this->height = $sizeVariant->height;
		$this->width = $sizeVariant->width;
		$this->url = !$noUrl ? $sizeVariant->url : null;
	}
}
