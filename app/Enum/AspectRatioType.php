<?php

namespace App\Enum;

/**
 * Enum AlbumDecorationType.
 *
 * All the allowed sorting possibilities on Album
 */
enum AspectRatioType: string
{
	case _3x2 = '3x2';
	case _1x1 = '1x1';
	case _2x3 = '2x3';
	case _16x9 = '16x9';

	public function css(): string
	{
		return match ($this) {
			self::_3x2 => 'ready',
			self::_1x1 => 'aspect-square',
			self::_2x3 => 'ready',
			self::_16x9 => 'aspect-video',
		};
	}
}
