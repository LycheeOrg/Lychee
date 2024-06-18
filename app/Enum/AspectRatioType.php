<?php

declare(strict_types=1);

namespace App\Enum;

/**
 * Enum AlbumDecorationType.
 *
 * All the allowed sorting possibilities on Album
 */
enum AspectRatioType: string
{
	case aspect5by4 = '5/4';
	case aspect3by2 = '3/2';
	case aspect1by1 = '1/1';
	case aspect2by3 = '2/3';
	case aspect4by5 = '4/5';
	case aspect1byx9 = '16/9';

	public function css(): string
	{
		return match ($this) {
			self::aspect5by4 => 'aspect-5/4',
			self::aspect4by5 => 'aspect-4/5',
			self::aspect3by2 => 'aspect-3/2',
			self::aspect1by1 => 'aspect-square',
			self::aspect2by3 => 'aspect-2/3',
			self::aspect1byx9 => 'aspect-video',
		};
	}

	/**
	 * Convert the enum into it's translated format.
	 *
	 * @return array<string,string>
	 */
	public static function localized(): array
	{
		return [
			self::aspect5by4->value => __('aspect_ratio.5by4'),
			self::aspect4by5->value => __('aspect_ratio.4by5'),
			self::aspect2by3->value => __('aspect_ratio.2by3'),
			self::aspect3by2->value => __('aspect_ratio.3by2'),
			self::aspect1by1->value => __('aspect_ratio.1by1'),
			self::aspect1byx9->value => __('aspect_ratio.1byx9'),
		];
	}
}
