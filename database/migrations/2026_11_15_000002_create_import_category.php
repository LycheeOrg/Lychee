<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Feature 115: one "Import" settings category for import from server and from a URL (FR-115-15).
 */
return new class() extends Migration {
	public const CAT = 'import';

	/**
	 * Keys moved into the category: key => [former category, former order].
	 *
	 * @var array<string,array{0:string,1:int}>
	 */
	private const MOVED = [
		'delete_imported' => ['Image Processing', 20],
		'import_via_symlink' => ['Image Processing', 21],
		'skip_duplicates_early' => ['Image Processing', 23],
		'sync_delete_missing_photos' => ['Image Processing', 25],
		'sync_delete_missing_albums' => ['Image Processing', 26],
		'sync_dry_run' => ['Image Processing', 27],
		'import_via_url_forbidden_localhost' => ['Admin', 20],
		'import_via_url_forbidden_local_ip' => ['Admin', 21],
		'import_via_url_require_https' => ['Admin', 22],
		'import_via_url_forbidden_ports' => ['Admin', 23],
		'import_via_url_block_redirect' => ['Admin', 24],
	];

	public function up(): void
	{
		DB::table('config_categories')->insert([
			'cat' => self::CAT,
			'name' => 'Import',
			'description' => 'Settings for importing photos from a folder on the server and from a URL.',
			'order' => 8,
		]);

		DB::table('configs')->insert([
			[
				'key' => 'import_root_folder_as_album',
				'value' => '1',
				'cat' => self::CAT,
				'type_range' => '0|1',
				'description' => 'Import the selected folder as an album',
				'details' => 'When disabled, the content of the selected folder goes straight into the target album (or Unsorted at the gallery root) and its sub-folders become albums directly under the target.',
				'is_secret' => false,
				'level' => 0,
				'not_on_docker' => false,
				'is_expert' => false,
				'order' => 1,
			],
			[
				'key' => 'import_scope_to_owner',
				'value' => '1',
				'cat' => self::CAT,
				'type_range' => '0|1',
				'description' => 'Only reuse and delete the importer\'s own albums and photos',
				'details' => 'When enabled, an import never merges into, nor deletes, albums and photos owned by another user.',
				'is_secret' => false,
				'level' => 0,
				'not_on_docker' => false,
				'is_expert' => true,
				'order' => 2,
			],
			[
				'key' => 'sync_users_server_paths',
				'value' => '0',
				'cat' => self::CAT,
				'type_range' => '0|1',
				'description' => 'lychee:sync with no path syncs the import folder of every user',
				'details' => 'Users restricted to a server import path are synced one after the other, as themselves, into the gallery root.',
				'is_secret' => false,
				'level' => 0,
				'not_on_docker' => false,
				'is_expert' => true,
				'order' => 3,
			],
		]);

		$order = 4;
		foreach (array_keys(self::MOVED) as $key) {
			DB::table('configs')->where('key', '=', $key)->update(['cat' => self::CAT, 'order' => $order]);
			$order++;
		}
	}

	public function down(): void
	{
		foreach (self::MOVED as $key => [$cat, $order]) {
			DB::table('configs')->where('key', '=', $key)->update(['cat' => $cat, 'order' => $order]);
		}

		DB::table('configs')->whereIn('key', ['import_root_folder_as_album', 'import_scope_to_owner', 'sync_users_server_paths'])->delete();
		DB::table('config_categories')->where('cat', '=', self::CAT)->delete();
	}
};
