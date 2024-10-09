<?php

namespace App\Http\Resources\Statistics;

use App\Enum\SizeVariantType;
use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript()]
class Sizes extends Data
{
	public SizeVariantType $type;
	public string $label;
	public int $size;

	/**
	 * @param array{type:SizeVariantType,size:int} $sizes
	 *
	 * @return void
	 */
	public function __construct(array $sizes)
	{
		$this->type = $sizes['type'];
		$this->label = $sizes['type']->localization();
		$this->size = $sizes['size'];
	}

	/**
	 * @param array{type:SizeVariantType,size:int} $sizes
	 *
	 * @return Sizes
	 */
	public static function fromArray(array $sizes): self
	{
		return new self($sizes);
	}
}
