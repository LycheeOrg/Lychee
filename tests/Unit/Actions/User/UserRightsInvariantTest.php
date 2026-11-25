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

namespace Tests\Unit\Actions\User;

use App\Actions\User\Create;
use App\Actions\User\Save;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\AbstractTestCase;

/**
 * Feature 118: upload implies share and edit (FR-118-22, S-118-20).
 */
class UserRightsInvariantTest extends AbstractTestCase
{
	use DatabaseTransactions;

	public function testCreateWithUploadGrantsShareAndEdit(): void
	{
		$user = resolve(Create::class)->do('rights-upload', 'a-password', may_upload: true, may_share: false, may_edit: false);

		$user = $user->fresh();
		self::assertTrue($user->may_upload);
		self::assertTrue($user->may_share);
		self::assertTrue($user->may_edit);
	}

	public function testCreateWithoutUploadKeepsGivenRights(): void
	{
		$share_only = resolve(Create::class)->do('rights-share', 'a-password', may_upload: false, may_share: true, may_edit: false);
		$edit_only = resolve(Create::class)->do('rights-edit', 'a-password', may_upload: false, may_share: false, may_edit: true);
		$none = resolve(Create::class)->do('rights-none', 'a-password');

		$share_only = $share_only->fresh();
		$edit_only = $edit_only->fresh();
		$none = $none->fresh();
		self::assertTrue($share_only->may_share);
		self::assertFalse($share_only->may_edit);
		self::assertFalse($edit_only->may_share);
		self::assertTrue($edit_only->may_edit);
		self::assertFalse($none->may_share);
		self::assertFalse($none->may_edit);
	}

	public function testSaveWithUploadGrantsShareAndEdit(): void
	{
		$user = User::factory()->create();

		resolve(Save::class)->do($user, $user->username, null, may_upload: true, may_edit_own_settings: true, may_share: false, may_edit: false);

		$user = $user->fresh();
		self::assertTrue($user->may_upload);
		self::assertTrue($user->may_share);
		self::assertTrue($user->may_edit);
	}

	public function testSaveWithoutUploadKeepsGivenRights(): void
	{
		$user = User::factory()->may_upload()->create();

		resolve(Save::class)->do($user, $user->username, null, may_upload: false, may_edit_own_settings: true, may_share: true, may_edit: false);

		$user = $user->fresh();
		self::assertFalse($user->may_upload);
		self::assertTrue($user->may_share);
		self::assertFalse($user->may_edit);
	}

	public function testFactoryUploadStateSetsAllThree(): void
	{
		$user = User::factory()->may_upload()->create()->fresh();

		self::assertTrue($user->may_share);
		self::assertTrue($user->may_edit);
	}
}
