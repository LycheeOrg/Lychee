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

namespace Tests\Unit\Database;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\AbstractTestCase;

/**
 * Feature 118: FR-118-01, FR-118-08, FR-118-19, S-118-01.
 */
class UserRightsMigrationTest extends AbstractTestCase
{
	use DatabaseTransactions;

	private function rightsMigration(): object
	{
		return require base_path('database/migrations/2026_11_25_000001_add_share_and_edit_rights_to_users.php');
	}

	private function settingsMigration(): object
	{
		return require base_path('database/migrations/2026_11_25_000002_add_grant_new_user_share_edit_rights.php');
	}

	public function testColumnsExist(): void
	{
		self::assertTrue(Schema::hasColumn('users', 'may_share'));
		self::assertTrue(Schema::hasColumn('users', 'may_edit'));
	}

	public function testBackfillCopiesUploadRight(): void
	{
		$uploader = User::factory()->may_upload()->create();
		$viewer = User::factory()->create();
		DB::table('users')->update(['may_share' => true, 'may_edit' => true]);
		DB::table('users')->where('id', '=', $viewer->id)->update(['may_upload' => false]);
		DB::table('users')->where('id', '=', $uploader->id)->update(['may_share' => false, 'may_edit' => false]);

		$this->rightsMigration()->backfill();

		$uploader = $uploader->fresh();
		$viewer = $viewer->fresh();
		self::assertTrue($uploader->may_share);
		self::assertTrue($uploader->may_edit);
		self::assertFalse($viewer->may_share);
		self::assertFalse($viewer->may_edit);
	}

	public function testSettingsExist(): void
	{
		self::assertSame(1, DB::table('configs')->where('key', '=', 'grant_new_user_share_rights')->count());
		self::assertSame(1, DB::table('configs')->where('key', '=', 'grant_new_user_edit_rights')->count());
		self::assertSame('Users Management', DB::table('configs')->where('key', '=', 'grant_new_user_share_rights')->value('cat'));
		self::assertSame('Users Management', DB::table('configs')->where('key', '=', 'grant_new_user_edit_rights')->value('cat'));
	}

	public function testSettingsCopyUploadSettingWhenOn(): void
	{
		DB::table('configs')->where('key', '=', 'grant_new_user_upload_rights')->update(['value' => '1']);
		DB::table('configs')->whereIn('key', ['grant_new_user_share_rights', 'grant_new_user_edit_rights'])->update(['value' => '0']);

		$this->settingsMigration()->copyUploadSetting();

		self::assertSame('1', DB::table('configs')->where('key', '=', 'grant_new_user_share_rights')->value('value'));
		self::assertSame('1', DB::table('configs')->where('key', '=', 'grant_new_user_edit_rights')->value('value'));
	}

	public function testSettingsCopyUploadSettingWhenOff(): void
	{
		DB::table('configs')->where('key', '=', 'grant_new_user_upload_rights')->update(['value' => '0']);
		DB::table('configs')->whereIn('key', ['grant_new_user_share_rights', 'grant_new_user_edit_rights'])->update(['value' => '1']);

		$this->settingsMigration()->copyUploadSetting();

		self::assertSame('0', DB::table('configs')->where('key', '=', 'grant_new_user_share_rights')->value('value'));
		self::assertSame('0', DB::table('configs')->where('key', '=', 'grant_new_user_edit_rights')->value('value'));
	}
}
