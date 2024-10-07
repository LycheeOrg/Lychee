<?php

namespace App\Http\Resources\Statistics;

use App\Enum\SizeVariantType;
use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript()]
class All extends Data
{
	/** @var array<int,Sizes> */
	public array $sizes;

	/**
	 * @param array<int,Sizes> $sizes
	 *
	 * @return void
	 */
	public function __construct(array $sizes)
	{
		$this->sizes = $sizes;
	}

	/**
	 * @param array{type:SizeVariantType,size:string,formatted:string}[] $sizes
	 *
	 * @return All
	 */
	public static function fromDTO(array $sizes): self
	{
		return new self(
			sizes: Sizes::collect($sizes),
		);
	}
}
