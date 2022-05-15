<?php

namespace App\Enum;

/**
 * @method static self NONE()
 * @method static self DESC()
 * @method static self EXIF()
 * @method static self DATE()
 */
final class PhotoOverlayMode extends LivewireEnum
{
	/**
	 * Iterate to the next OverlayMode.
	 *
	 * @return PhotoOverlayMode
	 */
	public function next(): PhotoOverlayMode
	{
		return match ($this) {
			self::NONE() => self::DESC(),
			self::DESC() => self::DATE(),
			self::DATE() => self::EXIF(),
			self::EXIF() => self::NONE(),
			default => self::NONE()
		};
	}

	/**
	 * Number of valid values.
	 *
	 * @return int
	 */
	public function count(): int
	{
		return 4;
	}
}
