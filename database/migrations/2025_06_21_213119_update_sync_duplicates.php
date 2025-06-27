<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

use App\Models\Extensions\BaseConfigMigration;

return new class() extends BaseConfigMigration {
	public const CONFIG = 'Image Processing';

	public function getConfigs(): array
	{
		// Fix the order of existing configs
		DB::table('configs')->where('key', 'delete_imported')->update(['order' => 20]);
		DB::table('configs')->where('key', 'import_via_symlink')->update(['order' => 21]);
		DB::table('configs')->where('key', 'skip_duplicates')->update(['order' => 22]);

		return [
			[
				'key' => 'skip_duplicates_early',
				'value' => '1',
				'cat' => self::CONFIG,
				'type_range' => self::BOOL,
				'description' => 'Skip duplicate early if found on import via the sync command.',
				'details' => 'Use the photo title to check for duplicate in the target album.',
				'is_secret' => false,
				'is_expert' => false,
				'level' => 0,
				'order' => 23,
			],
			[
				'key' => 'sync_delete_missing_photos',
				'value' => '0',
				'cat' => self::CONFIG,
				'type_range' => self::BOOL,
				'description' => 'Photos in Lychee not present in the synced directory will be deleted from their target album.',
				'details' => 'This option is only enabled if dry_run is disabled.',
				'is_secret' => false,
				'is_expert' => true,
				'level' => 0,
				'order' => 24,
			],
			[
				'key' => 'sync_delete_missing_albums',
				'value' => '0',
				'cat' => self::CONFIG,
				'type_range' => self::BOOL,
				'description' => 'Albums in Lychee not present in the synced directory will be deleted from the tree.',
				'details' => 'This option is only enabled if dry_run is disabled.',
				'is_secret' => false,
				'is_expert' => true,
				'level' => 0,
				'order' => 25,
			],
			[
				'key' => 'sync_dry_run',
				'value' => '1',
				'cat' => self::CONFIG,
				'type_range' => self::BOOL,
				'description' => 'Run the destructive part of the sync command in dry-run mode.',
				'details' => '<span class="pi pi-exclamation-triangle text-orange-500"></span> If disabled this will allow the sync command to delete albums/photos from your Lychee instance.',
				'is_secret' => false,
				'is_expert' => true,
				'level' => 0,
				'order' => 26,
			],
		];
	}
};