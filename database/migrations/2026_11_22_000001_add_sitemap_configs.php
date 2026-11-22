<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

use App\Models\Extensions\BaseConfigMigration;

/**
 * Feature 117: settings of `GET /sitemap.xml` (CFG-117-01 … 03).
 * No category of its own: the two toggles sit in Basics next to the embeds,
 * the sensitive-album switch in Sensitive next to the other hide_nsfw_in_* keys.
 */
return new class() extends BaseConfigMigration {
	public const BASICS = 'config';
	public const SENSITIVE = 'Mod NSFW';
	public const REQUIRES = 'sitemap_enable';
	public const ENABLE_DETAILS = 'Lists the albums a guest can browse from the gallery root, without photos. Submit the address of this gallery followed by <code>/sitemap.xml</code> to Google Search Console or other search engines. Not served while login is required or a gallery password is set.';

	public function getConfigs(): array
	{
		return [
			[
				'key' => 'sitemap_enable',
				'value' => '0',
				'cat' => self::BASICS,
				'type_range' => self::BOOL,
				'is_secret' => false,
				'description' => 'Enable the sitemap for search engines',
				'details' => self::ENABLE_DETAILS,
				'level' => 0,
				'order' => 103,
				'is_expert' => false,
				'required_keys' => null,
			],
			[
				'key' => 'sitemap_root_only',
				'value' => '0',
				'cat' => self::BASICS,
				'type_range' => self::BOOL,
				'is_secret' => false,
				'description' => 'Only list the gallery root and top-level albums in the sitemap',
				'details' => 'Albums inside other albums are left out of the sitemap.',
				'level' => 0,
				'order' => 104,
				'is_expert' => false,
				'required_keys' => self::REQUIRES,
			],
			[
				'key' => 'hide_nsfw_in_sitemap',
				'value' => '1',
				'cat' => self::SENSITIVE,
				'type_range' => self::BOOL,
				'is_secret' => false,
				'description' => 'Do not list sensitive albums in the sitemap',
				'details' => 'Sensitive albums and every album inside them are left out of the sitemap.',
				'level' => 0,
				'order' => 32767,
				'is_expert' => false,
				'required_keys' => self::REQUIRES,
			],
		];
	}
};
