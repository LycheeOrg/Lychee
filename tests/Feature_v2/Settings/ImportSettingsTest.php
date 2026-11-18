<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

/**
 * We don't care for unhandled exceptions in tests.
 * It is the nature of a test to throw an exception.
 * Without this suppression we had 100+ Linter warning in this file which
 * don't help anything.
 *
 * @noinspection PhpDocMissingThrowsInspection
 * @noinspection PhpUnhandledExceptionInspection
 */

namespace Tests\Feature_v2\Settings;

use Illuminate\Support\Facades\DB;
use Tests\Feature_v2\Base\BaseApiWithDataTest;

/**
 * Feature 115: FR-115-15, S-115-40.
 */
class ImportSettingsTest extends BaseApiWithDataTest
{
	private const KEYS = [
		'import_root_folder_as_album' => '1',
		'import_scope_to_owner' => '1',
		'sync_users_server_paths' => '0',
		'delete_imported' => '0',
		'import_via_symlink' => '0',
		'skip_duplicates_early' => '1',
		'sync_delete_missing_photos' => '0',
		'sync_delete_missing_albums' => '0',
		'sync_dry_run' => '1',
		'import_via_url_forbidden_localhost' => '1',
		'import_via_url_forbidden_local_ip' => '1',
		'import_via_url_require_https' => '1',
		'import_via_url_forbidden_ports' => '1',
		'import_via_url_block_redirect' => '1',
	];

	public function testCategoryHoldsImportSettingsInOrder(): void
	{
		$category = DB::table('config_categories')->where('cat', '=', 'import')->first();
		self::assertNotNull($category);
		self::assertSame('Import', $category->name);
		self::assertSame('Settings for importing photos from a folder on the server and from a URL.', $category->description);
		self::assertGreaterThan(6, intval($category->order));
		self::assertLessThan(10, intval($category->order));

		$rows = DB::table('configs')->where('cat', '=', 'import')->orderBy('order')->get(['key', 'value']);
		self::assertSame(array_keys(self::KEYS), $rows->pluck('key')->all());
		self::assertSame(array_values(self::KEYS), $rows->pluck('value')->all());
	}

	public function testSkipDuplicatesStaysInImageProcessing(): void
	{
		self::assertSame('Image Processing', DB::table('configs')->where('key', '=', 'skip_duplicates')->value('cat'));
		self::assertSame('Image Processing', DB::table('configs')->where('key', '=', 'skip_duplicates_not_owned')->value('cat'));
	}

	public function testSettingsListShowsCategory(): void
	{
		$response = $this->actingAs($this->admin)->getJson('Settings');
		$this->assertOk($response);
		$category = collect($response->json())->firstWhere('cat', 'import');
		self::assertNotNull($category);
		self::assertSame('Import', $category['name']);
		self::assertNotSame('', $category['description']);
	}
}
