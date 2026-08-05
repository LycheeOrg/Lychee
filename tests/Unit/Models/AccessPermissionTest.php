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

namespace Tests\Unit\Models;

use App\Constants\AccessPermissionConstants as APC;
use App\Models\AccessPermission;
use App\Repositories\ConfigManager;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Mockery\MockInterface;
use Tests\AbstractTestCase;

class AccessPermissionTest extends AbstractTestCase
{
	use DatabaseTransactions;

	public function testCreateFullAccess(): void
	{
		$ap = AccessPermission::withGrantFullPermissionsToUser(1);

		self::assertEquals(1, $ap->user_id);
		self::assertTrue($ap->{APC::GRANTS_FULL_PHOTO_ACCESS}); // @phpstan-ignore-line: Variable property access on App\Models\AccessPermission
		self::assertTrue($ap->{APC::GRANTS_DOWNLOAD}); // @phpstan-ignore-line: Variable property access on App\Models\AccessPermission
		self::assertTrue($ap->{APC::GRANTS_UPLOAD}); // @phpstan-ignore-line: Variable property access on App\Models\AccessPermission
		self::assertTrue($ap->{APC::GRANTS_EDIT}); // @phpstan-ignore-line: Variable property access on App\Models\AccessPermission
		self::assertTrue($ap->{APC::GRANTS_DELETE}); // @phpstan-ignore-line: Variable property access on App\Models\AccessPermission
	}

	private function mockConfigManager(bool $full_access, bool $download): void
	{
		$this->mock(ConfigManager::class, function (MockInterface $mock) use ($full_access, $download): void {
			$mock->shouldReceive('getValueAsBool')->with('grants_full_photo_access')->andReturn($full_access);
			$mock->shouldReceive('getValueAsBool')->with('grants_download')->andReturn($download);
		});
	}

	public function testOfPublicUsesConfigDefaultsAndIsNotLinkRequired(): void
	{
		$this->mockConfigManager(full_access: true, download: false);

		$ap = AccessPermission::ofPublic();

		self::assertFalse($ap->is_link_required);
		self::assertTrue($ap->grants_full_photo_access);
		self::assertFalse($ap->grants_download);
		self::assertFalse($ap->grants_upload);
		self::assertFalse($ap->grants_edit);
		self::assertFalse($ap->grants_delete);
		self::assertNull($ap->password);
	}

	public function testOfPublicHiddenIsLinkRequired(): void
	{
		$this->mockConfigManager(full_access: false, download: true);

		$ap = AccessPermission::ofPublicHidden();

		self::assertTrue($ap->is_link_required);
		self::assertFalse($ap->grants_full_photo_access);
		self::assertTrue($ap->grants_download);
	}

	public function testOfAccessPermissionReplicatesWithoutPasswordOrAlbum(): void
	{
		$original = AccessPermission::factory()->create([
			APC::BASE_ALBUM_ID => null,
			APC::PASSWORD => 'super-secret',
			APC::GRANTS_DOWNLOAD => true,
		]);

		$copy = AccessPermission::ofAccessPermission($original);

		self::assertNull($copy->password);
		self::assertNull($copy->base_album_id);
		self::assertTrue($copy->grants_download);
		self::assertFalse($copy->exists);
	}
}
