<?php

namespace App\Enum;

enum ConvertableImageType: string
{
	case HEIC = 'heic';
	case HEIF = 'heif';

	public static function isHeifImageType(string $extension): bool
	{
		$extension = str($extension)->lower()->toString();
		
		return in_array($extension, [
			self::HEIC->value,
			self::HEIF->value,
		], true);
	}
}