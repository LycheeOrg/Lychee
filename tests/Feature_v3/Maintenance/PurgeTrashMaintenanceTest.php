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

namespace Tests\Feature_v3\Maintenance;

use App\Actions\Photo\Trash;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\DB;
use Tests\Feature_v3\Base\BaseApiWithDataTest;
use Tests\Traits\CreatesBulkFixtures;

/**
 * Feature 119 (FR-119-05, API-119-01/02, S-119-10): `Maintenance::purgeTrash`
 * shows and finishes pending deletions. The route is v2, reached through the
 * inherited helpers.
 */
class PurgeTrashMaintenanceTest extends BaseApiWithDataTest
{
	use CreatesBulkFixtures;

	public function setUp(): void
	{
		parent::setUp();
		Bus::fake();
		(new Trash())->everywhere([$this->photo1->id, $this->photo1b->id]);
	}

	public function testGuestIsUnauthorized(): void
	{
		$this->assertUnauthorized($this->getJson('Maintenance::purgeTrash'));
		$this->assertUnauthorized($this->postJson('Maintenance::purgeTrash'));
	}

	public function testUserIsForbidden(): void
	{
		$this->assertForbidden($this->actingAs($this->userMayUpload1)->getJson('Maintenance::purgeTrash'));
		$this->assertForbidden($this->actingAs($this->userMayUpload1)->postJson('Maintenance::purgeTrash'));
		self::assertSame(2, DB::table('trashed_photos')->count());
	}

	public function testAdminSeesTheCountAndPurges(): void
	{
		$response = $this->actingAs($this->admin)->getJson('Maintenance::purgeTrash');
		$this->assertOk($response);
		self::assertSame(2, $response->json());

		$this->listenToBindings();
		$this->assertNoContent($this->actingAs($this->admin)->postJson('Maintenance::purgeTrash'));

		self::assertSame(0, DB::table('trashed_photos')->count());
		$this->assertDatabaseMissing('photos', ['id' => $this->photo1->id]);
		$this->assertBindingsWithinLimit();

		$response = $this->actingAs($this->admin)->getJson('Maintenance::purgeTrash');
		self::assertSame(0, $response->json());
	}
}
