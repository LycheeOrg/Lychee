<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\DTO;

use App\Exceptions\Internal\LycheeLogicException;
use Illuminate\Contracts\Support\Arrayable;

/**
 * In some cases, when a DTO does not need to apply casts on attributes
 * we can directly make use of a reflection which returns an array containing
 * all the PUBLIC attributes of the DTO.
 *
 * @extends AbstractDTO<mixed>
 */
class ArrayableDTO extends AbstractDTO
{
	/**
	 * By default, we return an array containing the PUBLIC attributes of the DTO.
	 *
	 * @return array<string,mixed> the serialized properties of this object
	 */
	public function toArray(): array
	{
		$result = [];
		$cls = new \ReflectionClass($this);
		$props = $cls->getProperties(\ReflectionProperty::IS_PUBLIC);
		foreach ($props as $prop) {
			$property_value = $prop->getValue($this);
			if (is_object($property_value)) {
				if ($property_value instanceof Arrayable) {
					// @codeCoverageIgnoreStart
					$property_value = $property_value->toArray();
				// @codeCoverageIgnoreEnd
				} elseif ($property_value instanceof \BackedEnum) {
					$property_value = $property_value->value;
				} else {
					// @codeCoverageIgnoreStart
					throw new LycheeLogicException(sprintf('Unable to convert %s into an array', get_class($property_value)));
					// @codeCoverageIgnoreEnd
				}
			}
			$result[$prop->getName()] = $property_value;
		}

		return $result;
	}
}