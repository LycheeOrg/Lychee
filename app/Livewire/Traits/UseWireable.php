<?php

declare(strict_types=1);

namespace App\Livewire\Traits;

use App\Exceptions\Internal\LycheeLogicException;

/**
 * Quick helpers for the serialization and deserialization of livewire components.
 *
 * @template TModelClass
 */
trait UseWireable
{
	/**
	 * @return array<string,mixed>
	 *
	 * @throws LycheeLogicException
	 */
	public function toLivewire(): array
	{
		$result = [];
		$cls = new \ReflectionClass($this);
		$props = $cls->getProperties(\ReflectionProperty::IS_PUBLIC);

		foreach ($props as $prop) {
			$propertyValue = $prop->getValue($this);
			if (is_object($propertyValue)) {
				throw new LycheeLogicException(sprintf('Convertion of %s is not supported', get_class($propertyValue)));
			}
			$result[$prop->getName()] = $propertyValue;
		}

		return $result;
	}

	/**
	 * @param mixed $data
	 *
	 * @return TModelClass
	 *
	 * @throws LycheeLogicException
	 * @throws \ReflectionException
	 */
	public static function fromLivewire(mixed $data)
	{
		if (!is_array($data)) {
			throw new LycheeLogicException('data is not an array');
		}

		$cls = new \ReflectionClass(self::class);

		return $cls->newInstanceArgs($data);
	}
}