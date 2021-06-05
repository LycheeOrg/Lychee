<?php

namespace App\Models\Extensions;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Trait HasAttributesPatch.
 *
 * Patch for [Laravel issue #37607](https://github.com/laravel/framework/issues/37607).
 */
trait HasAttributesPatch
{
	protected function mutateAttributeForArray($key, $value)
	{
		if ($this->hasGetMutator($key)) {
			$value = $this->mutateAttribute($key, $value);
		}
		if ($this->isClassCastable($key)) {
			$value = $this->getClassCastableAttributeValue($key, $value);
		}

		return $value instanceof Arrayable ? $value->toArray() : $value;
	}
}
