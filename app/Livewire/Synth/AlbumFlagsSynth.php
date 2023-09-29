<?php

namespace App\Livewire\Synth;

use App\Exceptions\Internal\LycheeLogicException;
use App\Livewire\DTO\AlbumFlags;
use Livewire\Mechanisms\HandleComponents\Synthesizers\Synth;

class AlbumFlagsSynth extends Synth
{
	public static string $key = 'af';

	public static function match(mixed $target): bool
	{
		return $target instanceof AlbumFlags;
	}

	/**
	 * @param AlbumFlags $target
	 *
	 * @return array<int,array<string,bool>>
	 */
	public function dehydrate($target): array
	{
		$result = [];
		$cls = new \ReflectionClass(AlbumFlags::class);
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
	 * @return AlbumFlags
	 */
	public function hydrate($value): AlbumFlags
	{
		$cls = new \ReflectionClass(AlbumFlags::class);

		/** @var AlbumFlags $flags */
		$flags = $cls->newInstanceWithoutConstructor();
		$props = $cls->getProperties(\ReflectionProperty::IS_PUBLIC);
		foreach ($props as $prop) {
			$flags->{$prop->getName()} = $value[$prop->getName()];
		}

		return $flags;
	}

	/**
	 * @param AlbumFlags $target
	 * @param string       $key
	 *
	 * @return string
	 */
	public function get(&$target, $key)
	{
		return $target->{$key};
	}

	/**
	 * @param AlbumFlags $target
	 * @param string       $key
	 * @param bool         $value
	 *
	 * @return void
	 */
	public function set(&$target, $key, $value)
	{
		$target->{$key} = $value;
	}
}