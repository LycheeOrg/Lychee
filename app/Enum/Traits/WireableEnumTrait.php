<?php

namespace App\Enum\Traits;

use App\Exceptions\Internal\LycheeLogicException;
use Closure;

trait WireableEnumTrait
{
	public function toLivewire(): string|int
	{
		return $this->value;
	}

	public static function fromLivewire(mixed $value): self
	{
		if (!is_string($value) && !is_int($value)) {
			throw new LycheeLogicException('Enum could not be instanciated from ' . strval($value), null);
		}

		return self::from($value);
	}

	/**
	 * @return string[]|int[]|\Closure
	 *
	 * @psalm-return array<string, string|int> | Closure(string):(int|string)
	 */
	protected static function values()
	{
		return function (string $name): string {
			return mb_strtolower($name);
		};
	}
}