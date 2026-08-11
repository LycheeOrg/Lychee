<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

use App\Models\Extensions\BaseConfigMigration;

return new class() extends BaseConfigMigration {
	public const MOD_WELCOME = 'Mod Welcome';
	public const LAYOUT = 'classic|portfolio|minimal|studio';
	public const TEXT_POSITION = 'top_left|top_right|bottom_left|bottom_right|center';
	public const ANIMATION_PRESET = 'none|classic_fade|zoom_in|parallax_scroll|slide_reveal';
	public const FEATURED_ITEMS_MODE = 'automatic|manual';
	public const OPACITY_RANGE = 'int:0:100';
	public const FEATURED_COUNT_RANGE = 'int:3:12';

	public function getConfigs(): array
	{
		return [
			[
				'key' => 'landing_layout',
				'value' => 'classic',
				'cat' => self::MOD_WELCOME,
				'type_range' => self::LAYOUT,
				'description' => 'Landing page layout',
				'details' => 'Options: classic (default, free), portfolio/minimal/studio (require Lychee SE).',
				'is_secret' => false,
				'is_expert' => false,
				'level' => 0,
				'order' => 13,
			],
			[
				'key' => 'landing_intro_screen_enabled',
				'value' => '1',
				'cat' => self::MOD_WELCOME,
				'type_range' => self::BOOL,
				'description' => 'Enable the animated intro splash screen',
				'details' => 'Applies to the classic and portfolio layouts. Disable to skip straight to the hero.',
				'is_secret' => false,
				'is_expert' => false,
				'level' => 0,
				'order' => 14,
			],
			[
				'key' => 'landing_hero_text_position',
				'value' => 'center',
				'cat' => self::MOD_WELCOME,
				'type_range' => self::TEXT_POSITION,
				'description' => 'Hero headline/subtitle/CTA position',
				'details' => 'Applies to the classic and portfolio layouts.',
				'is_secret' => false,
				'is_expert' => false,
				'level' => 0,
				'order' => 15,
			],
			[
				'key' => 'landing_hero_text_color',
				'value' => '',
				'cat' => self::MOD_WELCOME,
				'type_range' => 'color',
				'description' => 'Hero headline/subtitle text color',
				'details' => 'Leave empty to use the default white. Applies to the headline and subtitle text only, on every layout.',
				'is_secret' => false,
				'is_expert' => false,
				'level' => 0,
				'order' => 16,
			],
			[
				'key' => 'landing_hero_text_opacity',
				'value' => '100',
				'cat' => self::MOD_WELCOME,
				'type_range' => self::OPACITY_RANGE,
				'description' => 'Hero headline/subtitle text opacity (%)',
				'details' => 'Range 0-100. Applies to the headline and subtitle text only, on every layout.',
				'is_secret' => false,
				'is_expert' => false,
				'level' => 0,
				'order' => 17,
			],
			[
				'key' => 'landing_animation_preset',
				'value' => 'classic_fade',
				'cat' => self::MOD_WELCOME,
				'type_range' => self::ANIMATION_PRESET,
				'description' => 'Landing page animation preset',
				'details' => 'Options: none/classic_fade (free), zoom_in/parallax_scroll/slide_reveal (require Lychee SE).',
				'is_secret' => false,
				'is_expert' => false,
				'level' => 0,
				'order' => 18,
			],
			[
				'key' => 'landing_about_enabled',
				'value' => '0',
				'cat' => self::MOD_WELCOME,
				'type_range' => self::BOOL,
				'description' => 'Enable the about section',
				'details' => 'Applies to the portfolio and minimal layouts.',
				'is_secret' => false,
				'is_expert' => false,
				'level' => 0,
				'order' => 19,
			],
			[
				'key' => 'landing_about_text',
				'value' => '',
				'cat' => self::MOD_WELCOME,
				'type_range' => self::STRING,
				'description' => 'About section text',
				'details' => 'Admin-authored HTML, rendered verbatim (same trust model as the footer additional text).',
				'is_secret' => false,
				'is_expert' => false,
				'level' => 0,
				'order' => 20,
			],
			[
				'key' => 'landing_featured_items_enabled',
				'value' => '0',
				'cat' => self::MOD_WELCOME,
				'type_range' => self::BOOL,
				'description' => 'Enable the featured content section (SE)',
				'details' => 'Applies to the portfolio layout only. Requires Lychee SE to take effect.',
				'is_secret' => false,
				'is_expert' => false,
				'level' => 0,
				'order' => 21,
			],
			[
				'key' => 'landing_featured_items_mode',
				'value' => 'automatic',
				'cat' => self::MOD_WELCOME,
				'type_range' => self::FEATURED_ITEMS_MODE,
				'description' => 'Featured content mode',
				'details' => 'Automatic: most recently published public albums. Manual: admin-curated photos/albums.',
				'is_secret' => false,
				'is_expert' => false,
				'level' => 0,
				'order' => 22,
			],
			[
				'key' => 'landing_featured_items_count',
				'value' => '6',
				'cat' => self::MOD_WELCOME,
				'type_range' => self::FEATURED_COUNT_RANGE,
				'description' => 'Number of automatic featured items',
				'details' => 'Range 3-12. Only used in automatic featured content mode.',
				'is_secret' => false,
				'is_expert' => false,
				'level' => 0,
				'order' => 23,
			],
			[
				'key' => 'landing_cta_text',
				'value' => '',
				'cat' => self::MOD_WELCOME,
				'type_range' => self::STRING,
				'description' => 'Primary call-to-action text override',
				'details' => 'Leave empty to use each layout\'s default label.',
				'is_secret' => false,
				'is_expert' => false,
				'level' => 0,
				'order' => 24,
			],
		];
	}
};
