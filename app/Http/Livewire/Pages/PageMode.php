<?php

namespace App\Http\Livewire\Pages;

// when php8.1 is the min version, we can move to proper enum
// https://github.com/spatie/laravel-enum
// https://github.com/spatie/enum

use Closure;
use Livewire\Wireable;
use Spatie\Enum\Laravel\Enum;

/**
 * @method static self ALBUM()
 * @method static self ALBUMS()
 * @method static self PHOTO()
 * @method static self MAP()
 */
final class PageMode extends Enum implements Wireable
{
	public function toLivewire()
	{
		return $this->label;
	}

	public static function fromLivewire($value)
	{
		return self::from($value);
	}

	protected static function values(): Closure
	{
		return function (string $name): string|int {
			return mb_strtolower($name);
		};
	}
}
