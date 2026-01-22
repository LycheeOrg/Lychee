<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace Database\Seeders;

use App\Actions\Album\SetProtectionPolicy;
use App\Http\Resources\Models\Utils\AlbumProtectionPolicy;
use App\Models\Album;
use App\Models\Configs;
use Illuminate\Database\Seeder;

class DemoSeeder extends Seeder
{
	/**
	 * Seed the application's database.
	 */
	public function run(): void
	{
		$set_protections = new SetProtectionPolicy();

		$tulips_protection_policy = new AlbumProtectionPolicy(
			is_public: true,
			is_link_required: false,
			is_nsfw: false,
			grants_full_photo_access: false,
			grants_download: false,
			grants_upload: false,
			is_password_required: false,
		);
		/** @var Album $tulip_album */
		$tulip_album = Album::query()
			->select(['albums.*'])
			->join('base_albums', 'base_albums.id', '=', 'albums.id')
			->where('title', '=', 'Tulips')->first();
		$set_protections->do($tulip_album, $tulips_protection_policy, false, null);

		$cat_protection_policy = new AlbumProtectionPolicy(
			is_public: true,
			is_link_required: false,
			is_nsfw: true,
			grants_full_photo_access: true,
			grants_download: true,
			grants_upload: false,
			is_password_required: false,
		);
		/** @var Album $cat_album */
		$cat_album = Album::query()
			->select(['albums.*'])
			->join('base_albums', 'base_albums.id', '=', 'albums.id')
			->where('title', '=', 'Cat')->first();
		$set_protections->do($cat_album, $cat_protection_policy, false, null);

		Configs::set('nsfw_banner_blur_backdrop', true);
		Configs::set('nsfw_visible', true);
		Configs::set('nsfw_warning', true);
		Configs::set('landing_page_enable', true);
		Configs::set('landing_title', 'Lychee demo');
		Configs::set('landing_subtitle', 'admin account: admin admin');
	}
}
