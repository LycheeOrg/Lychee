<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Enum;

use App\Enum\Traits\DecorateBackedEnum;

/**
 * Defines the possible licenses available by Lychee.
 */
enum LicenseType: string
{
	use DecorateBackedEnum;

	case NONE = 'none';
	case RESERVED = 'reserved';
	case CC0 = 'CC0';
	case CC_BY_1_0 = 'CC-BY-1.0';
	case CC_BY_2_0 = 'CC-BY-2.0';
	case CC_BY_2_5 = 'CC-BY-2.5';
	case CC_BY_3_0 = 'CC-BY-3.0';
	case CC_BY_4_0 = 'CC-BY-4.0';
	case CC_BY_ND_1_0 = 'CC-BY-ND-1.0';
	case CC_BY_ND_2_0 = 'CC-BY-ND-2.0';
	case CC_BY_ND_2_5 = 'CC-BY-ND-2.5';
	case CC_BY_ND_3_0 = 'CC-BY-ND-3.0';
	case CC_BY_ND_4_0 = 'CC-BY-ND-4.0';
	case CC_BY_SA_1_0 = 'CC-BY-SA-1.0';
	case CC_BY_SA_2_0 = 'CC-BY-SA-2.0';
	case CC_BY_SA_2_5 = 'CC-BY-SA-2.5';
	case CC_BY_SA_3_0 = 'CC-BY-SA-3.0';
	case CC_BY_SA_4_0 = 'CC-BY-SA-4.0';
	case CC_BY_NC_1_0 = 'CC-BY-NC-1.0';
	case CC_BY_NC_2_0 = 'CC-BY-NC-2.0';
	case CC_BY_NC_2_5 = 'CC-BY-NC-2.5';
	case CC_BY_NC_3_0 = 'CC-BY-NC-3.0';
	case CC_BY_NC_4_0 = 'CC-BY-NC-4.0';
	case CC_BY_NC_ND_1_0 = 'CC-BY-NC-ND-1.0';
	case CC_BY_NC_ND_2_0 = 'CC-BY-NC-ND-2.0';
	case CC_BY_NC_ND_2_5 = 'CC-BY-NC-ND-2.5';
	case CC_BY_NC_ND_3_0 = 'CC-BY-NC-ND-3.0';
	case CC_BY_NC_ND_4_0 = 'CC-BY-NC-ND-4.0';
	case CC_BY_NC_SA_1_0 = 'CC-BY-NC-SA-1.0';
	case CC_BY_NC_SA_2_0 = 'CC-BY-NC-SA-2.0';
	case CC_BY_NC_SA_2_5 = 'CC-BY-NC-SA-2.5';
	case CC_BY_NC_SA_3_0 = 'CC-BY-NC-SA-3.0';
	case CC_BY_NC_SA_4_0 = 'CC-BY-NC-SA-4.0';

	/**
	 * Given return the array of localized name.
	 *
	 * @return array<string,string>
	 */
	public static function localized(): array
	{
		return [
			self::NONE->value => 'None',
			self::RESERVED->value => __('gallery.album_reserved'),
			self::CC0->value => 'CC0 - Public Domain',
			self::CC_BY_1_0->value => 'CC Attribution 1.0',
			self::CC_BY_2_0->value => 'CC Attribution 2.0',
			self::CC_BY_2_5->value => 'CC Attribution 2.5',
			self::CC_BY_3_0->value => 'CC Attribution 3.0',
			self::CC_BY_4_0->value => 'CC Attribution 4.0',
			self::CC_BY_ND_1_0->value => 'CC Attribution-NoDerivatives 1.0',
			self::CC_BY_ND_2_0->value => 'CC Attribution-NoDerivatives 2.0',
			self::CC_BY_ND_2_5->value => 'CC Attribution-NoDerivatives 2.5',
			self::CC_BY_ND_3_0->value => 'CC Attribution-NoDerivatives 3.0',
			self::CC_BY_ND_4_0->value => 'CC Attribution-NoDerivatives 4.0',
			self::CC_BY_SA_1_0->value => 'CC Attribution-ShareAlike 1.0',
			self::CC_BY_SA_2_0->value => 'CC Attribution-ShareAlike 2.0',
			self::CC_BY_SA_2_5->value => 'CC Attribution-ShareAlike 2.5',
			self::CC_BY_SA_3_0->value => 'CC Attribution-ShareAlike 3.0',
			self::CC_BY_SA_4_0->value => 'CC Attribution-ShareAlike 4.0',
			self::CC_BY_NC_1_0->value => 'CC Attribution-NonCommercial 1.0',
			self::CC_BY_NC_2_0->value => 'CC Attribution-NonCommercial 2.0',
			self::CC_BY_NC_2_5->value => 'CC Attribution-NonCommercial 2.5',
			self::CC_BY_NC_3_0->value => 'CC Attribution-NonCommercial 3.0',
			self::CC_BY_NC_4_0->value => 'CC Attribution-NonCommercial 4.0',
			self::CC_BY_NC_ND_1_0->value => 'CC Attribution-NonCommercial-NoDerivatives 1.0',
			self::CC_BY_NC_ND_2_0->value => 'CC Attribution-NonCommercial-NoDerivatives 2.0',
			self::CC_BY_NC_ND_2_5->value => 'CC Attribution-NonCommercial-NoDerivatives 2.5',
			self::CC_BY_NC_ND_3_0->value => 'CC Attribution-NonCommercial-NoDerivatives 3.0',
			self::CC_BY_NC_ND_4_0->value => 'CC Attribution-NonCommercial-NoDerivatives 4.0',
			self::CC_BY_NC_SA_1_0->value => 'CC Attribution-NonCommercial-ShareAlike 1.0',
			self::CC_BY_NC_SA_2_0->value => 'CC Attribution-NonCommercial-ShareAlike 2.0',
			self::CC_BY_NC_SA_2_5->value => 'CC Attribution-NonCommercial-ShareAlike 2.5',
			self::CC_BY_NC_SA_3_0->value => 'CC Attribution-NonCommercial-ShareAlike 3.0',
			self::CC_BY_NC_SA_4_0->value => 'CC Attribution-NonCommercial-ShareAlike 4.0',
		];
	}

	/**
	 * Return the localization string of current.
	 *
	 * @return string
	 */
	public function localization(): string
	{
		return self::localized()[$this->value];
	}
}
