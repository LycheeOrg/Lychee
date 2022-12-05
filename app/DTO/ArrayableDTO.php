<?php

namespace App\DTO;

use App\Exceptions\Internal\LycheeLogicException;
use Illuminate\Contracts\Support\Arrayable;

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
		$cls = new \ReflectionClass($this);
		$props = $cls->getProperties(\ReflectionProperty::IS_PUBLIC);
		foreach ($props as $prop) {
			$propertyValue = $prop->getValue($this);
			if (is_object($propertyValue)) {
				if ($propertyValue instanceof Arrayable) {
					$propertyValue = $propertyValue->toArray();
				} elseif ($propertyValue instanceof \BackedEnum) {
					$propertyValue = $propertyValue->value;
				} else {
					throw new LycheeLogicException(sprintf('Unable to convert %s into an array', get_class($propertyValue)));
				}
			}
			$result[$prop->getName()] = $propertyValue;
		}

		return $result;
	}
}