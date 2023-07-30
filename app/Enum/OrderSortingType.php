<?php

namespace App\Enum;

/**
 * Enum OrderSortingType.
 */
enum OrderSortingType: string
{
	case ASC = 'ASC';
	case DESC = 'DESC';

	/**
	 * Convert the enum into it's translated format.
	 * Note that it is missing owner.
	 *
	 * @return array<string,string>
	 */
	public static function toTranslation(): array
	{
		return [
			self::ASC->value => __('lychee.SORT_ASCENDING'),
			self::DESC->value => __('lychee.SORT_DESCENDING'),
		];
	}
}

