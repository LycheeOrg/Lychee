<?php

declare(strict_types=1);

namespace App\Livewire\Synth;

use App\Exceptions\Internal\LycheeLogicException;
use App\Livewire\DTO\SessionFlags;
use Livewire\Mechanisms\HandleComponents\Synthesizers\Synth;

class SessionFlagsSynth extends Synth
{
	public static string $key = 's';

	public static function match(mixed $target): bool
	{
		return $target instanceof SessionFlags;
	}

	/**
	 * @param SessionFlags $target
	 *
	 * @return array<int,array<string,bool>>
	 */
	public function dehydrate($target): array
	{
		$result = [];
		$cls = new \ReflectionClass(SessionFlags::class);
		$props = $cls->getProperties(\ReflectionProperty::IS_PUBLIC);
		foreach ($props as $prop) {
			$propertyValue = $prop->getValue($target);
			if (is_object($propertyValue)) {
				throw new LycheeLogicException(sprintf('wrong value type for %s', get_class($propertyValue)));
			}
			$result[$prop->getName()] = $propertyValue;
		}

		return [$result, []];
	}

	/**
	 * @param array<string,bool> $value
	 *
	 * @return SessionFlags
	 */
	public function hydrate($value): SessionFlags
	{
		$cls = new \ReflectionClass(SessionFlags::class);

		/** @var SessionFlags $flags */
		$flags = $cls->newInstanceWithoutConstructor();
		$props = $cls->getProperties(\ReflectionProperty::IS_PUBLIC);
		foreach ($props as $prop) {
			$flags->{$prop->getName()} = $value[$prop->getName()];
		}

		return $flags;
	}

	/**
	 * @param SessionFlags $target
	 * @param string       $key
	 *
	 * @return string
	 */
	public function get(&$target, $key)
	{
		return $target->{$key};
	}

	/**
	 * @param SessionFlags $target
	 * @param string       $key
	 * @param bool         $value
	 *
	 * @return void
	 */
	public function set(&$target, $key, $value)
	{
		$target->{$key} = $value;
		$target->save();
	}
}