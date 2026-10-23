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

use App\Jobs\RecomputeAllAlbumBucketsJob;
use App\Jobs\RecomputeAllPhotoBucketsJob;
use Illuminate\Support\Facades\Queue;
use Tests\Feature_v3\Base\BaseApiWithDataTest;

/**
 * `POST Maintenance::recomputeBuckets`: queues the rewrite of every album
 * and `photo_album` bucket key, with struct-of-arrays enabled only.
 */
class RecomputeBucketsTest extends BaseApiWithDataTest
{
	public function setUp(): void
	{
		parent::setUp();
		config(['features.struct-of-array' => true]);
		Queue::fake([RecomputeAllAlbumBucketsJob::class, RecomputeAllPhotoBucketsJob::class]);
	}

	public function testGuest(): void
	{
		$response = $this->postJson('Maintenance::recomputeBuckets');
		$this->assertUnauthorized($response);
		Queue::assertNothingPushed();
	}

	public function testUser(): void
	{
		$response = $this->actingAs($this->userLocked)->postJson('Maintenance::recomputeBuckets');
		$this->assertForbidden($response);
		Queue::assertNothingPushed();
	}

	public function testAdminQueuesAlbumAndPhotoRecompute(): void
	{
		$response = $this->actingAs($this->admin)->postJson('Maintenance::recomputeBuckets');
		$this->assertNoContent($response);
		Queue::assertPushed(RecomputeAllAlbumBucketsJob::class);
		Queue::assertPushed(RecomputeAllPhotoBucketsJob::class);
	}

	public function testAdminForbiddenWithStructOfArraysDisabled(): void
	{
		config(['features.struct-of-array' => false]);

		$response = $this->actingAs($this->admin)->postJson('Maintenance::recomputeBuckets');
		$this->assertForbidden($response);
		Queue::assertNothingPushed();
	}
}
