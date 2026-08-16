<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Enum;

/**
 * Enum LandingAnimationPreset.
 *
 * Defines the selectable landing page animation presets.
 */
enum LandingAnimationPreset: string
{
	case NONE = 'none';
	case CLASSIC_FADE = 'classic_fade';
	case ZOOM_IN = 'zoom_in';
	case PARALLAX_SCROLL = 'parallax_scroll';
	case SLIDE_REVEAL = 'slide_reveal';
}
