<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Models\Extensions;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Database\Eloquent\InvalidCastException;

/**
 * Trait HasAttributesPatch.
 *
 * Patch for [Laravel issue #37607](https://github.com/laravel/framework/issues/37607).
 */
trait HasAttributesPatch
{
	/**
	 * @throws InvalidCastException
	 */
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
