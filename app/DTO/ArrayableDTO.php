<?php

namespace App\DTO;

use ReflectionClass;
use ReflectionProperty;

/**
 * In some cases, when a DTO does not need to apply casts on attributes
 * we can directly make use of a reflection which returns an array containing
 * all the PUBLIC attributes of the DTO.
 */
class ArrayableDTO extends DTO
{
	/**
	 * By default, we return an array containing the PUBLIC attributes of the DTO.
	 *
	 * @return array the serialized properties of this object
	 */
	public function toArray(): array
	{
		$result = [];
		$cls = new ReflectionClass($this);
		$props = $cls->getProperties(ReflectionProperty::IS_PUBLIC);
		foreach ($props as $prop) {
			$result[$prop->getName()] = $prop->getValue($this);
		}

		return $result;
	}
}