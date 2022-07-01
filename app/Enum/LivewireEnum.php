<?php

namespace App\Enum;

// when php8.1 is the min version, we can move to proper enum
// https://github.com/spatie/laravel-enum
// https://github.com/spatie/enum

use App\Exceptions\Internal\LycheeLogicException;
use Closure;
use Livewire\Wireable;
use Spatie\Enum\Laravel\Enum;

abstract class LivewireEnum extends Enum implements Wireable
{
	public function toLivewire(): string
	{
		return $this->label;
	}

	public static function fromLivewire(mixed $value): LivewireEnum
	{
		if (!is_string($value)) {
			throw new LycheeLogicException('Enum could not be instanciated from ' . strval($value), null);
		}

		return self::from($value);
	}

	/**
	 * @return string[]|int[]|Closure
	 * @psalm-return array<string, string|int> | Closure(string):(int|string)
	 */
	protected static function values()
	{
		return function (string $name): string {
			return mb_strtolower($name);
		};
	}
}
