<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Enum;

/**
 * Enum AspectRatioCSSType.
 *
 * CSS mapping so that TypeScript knows what to expect.
 */
enum AspectRatioCSSType: string
{
	case aspect5by4 = 'aspect-5/4';
	case aspect4by5 = 'aspect-4/5';
	case aspect3by2 = 'aspect-3/2';
	case aspect1by1 = 'aspect-square';
	case aspect2by3 = 'aspect-2/3';
	case aspect1byx9 = 'aspect-video';
}
