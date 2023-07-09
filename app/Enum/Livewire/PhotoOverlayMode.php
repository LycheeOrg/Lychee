<?php

namespace App\Enum\Livewire;

use App\Enum\Traits\WireableEnumTrait;
use Livewire\Wireable;

enum PhotoOverlayMode: string implements Wireable
{
	use WireableEnumTrait;

	case NONE = 'none';
	case DESC = 'desc';
	case EXIF = 'exif';
	case DATE = 'date';

	/**
	 * Iterate to the next OverlayMode.
	 *
	 * @return PhotoOverlayMode
	 */
	public function next(): PhotoOverlayMode
	{
		return match ($this) {
			self::NONE => self::DESC,
			self::DESC => self::DATE,
			self::DATE => self::EXIF,
			self::EXIF => self::NONE
		};
	}

	/**
	 * Number of valid values.
	 *
	 * @return int
	 */
	public static function count(): int
	{
		return 4;
	}
}
