<?php

namespace App\Http\Requests\Traits;

trait HasSizeVariantTrait
{
	protected string $sizeVariant;

	/**
	 * @return string
	 */
	public function sizeVariant(): string
	{
		return $this->sizeVariant;
	}
}
