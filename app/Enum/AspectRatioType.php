<?php

namespace App\Enum;

/**
 * Enum AspectRatioType.
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

	public function css(): AspectRatioCSSType
	{
		return match ($this) {
			self::aspect5by4 => AspectRatioCSSType::aspect5by4,
			self::aspect4by5 => AspectRatioCSSType::aspect4by5,
			self::aspect3by2 => AspectRatioCSSType::aspect3by2,
			self::aspect1by1 => AspectRatioCSSType::aspect1by1,
			self::aspect2by3 => AspectRatioCSSType::aspect2by3,
			self::aspect1byx9 => AspectRatioCSSType::aspect1byx9,
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
