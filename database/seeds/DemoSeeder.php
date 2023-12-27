<?php

namespace Database\Seeders;

use App\Actions\Album\SetProtectionPolicy;
use App\DTO\AlbumProtectionPolicy;
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
		$setProtections = new SetProtectionPolicy();

		$tulipsProtectionPolicy = new AlbumProtectionPolicy(
			is_public: true,
			is_link_required: false,
			is_nsfw: false,
			grants_full_photo_access: false,
			grants_download: false,
			is_password_required: false,
		);
		/** @var Album $tulipAlbum */
		$tulipAlbum = Album::query()
			->select(['albums.*'])
			->join('base_albums', 'base_albums.id', '=', 'albums.id')
			->where('title', '=', 'Tulips')->first();
		$setProtections->do($tulipAlbum, $tulipsProtectionPolicy, false, null);

		$catProtectionPolicy = new AlbumProtectionPolicy(
			is_public: true,
			is_link_required: false,
			is_nsfw: true,
			grants_full_photo_access: true,
			grants_download: true,
			is_password_required: false,
		);
		/** @var Album $catAlbum */
		$catAlbum = Album::query()
			->select(['albums.*'])
			->join('base_albums', 'base_albums.id', '=', 'albums.id')
			->where('title', '=', 'Cat')->first();
		$setProtections->do($catAlbum, $catProtectionPolicy, false, null);

		Configs::set('nsfw_banner_blur_backdrop', true);
		Configs::set('nsfw_visible', true);
		Configs::set('nsfw_warning', true);
		Configs::set('allow_online_git_pull', false);
		Configs::set('force_migration_in_production', false);
		Configs::set('apply_composer_update', false);
		Configs::set('landing_page_enable', true);
		Configs::set('landing_title', 'Lychee demo');
		Configs::set('landing_subtitle', 'admin account: admin admin');
	}
}
