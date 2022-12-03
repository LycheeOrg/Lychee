<?php

namespace App\DTO;

use App\Exceptions\Internal\LycheeLogicException;
use Illuminate\Contracts\Support\Arrayable;

/**
 * In some cases, when a DTO does not need to apply casts on attributes
 * we can directly make use of a reflection which returns an array containing
 * all the PUBLIC attributes of the DTO.
 */
class ArrayableDTO extends AbstractDTO
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
			$value = $prop->getValue($this);
			if (is_object($value)) {
				if ($value instanceof Arrayable) {
					$value = $value->toArray();
				} else {
					throw new LycheeLogicException(sprintf('Unable to convert %s into an array', get_class($value)));
				}
			}
			$result[$prop->getName()] = $value;
		}

		return $result;
	}
}