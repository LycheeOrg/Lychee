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

namespace Tests\Feature_v3\Insights;

use Illuminate\Support\Facades\Route;
use Tests\Feature_v3\Base\BaseApiWithDataTest;

/**
 * The unused per-user space endpoint is gone; the counts endpoint of the v7
 * Statistics page and the storage endpoints used by the album drawer and
 * Insights stay (Feature 085, FR-085-02, FR-085-03, S-085-14).
 */
class StatisticsRemovedTest extends BaseApiWithDataTest
{
	public function setUp(): void
	{
		parent::setUp();
		$this->requireSe();
	}

	public function tearDown(): void
	{
		$this->resetSe();
		parent::tearDown();
	}

	public function testCountsOverTimeStaysForV7(): void
	{
		self::assertTrue($this->isRegistered('api/v2/Statistics::getCountsOverTime'));
		$this->assertOk($this->actingAs($this->admin)->getJsonWithData('Statistics::getCountsOverTime', ['type' => 'taken_at']));
	}

	public function testUserSpaceIsGone(): void
	{
		self::assertFalse($this->isRegistered('api/v2/Statistics::userSpace'));
		self::assertNotSame(200, $this->actingAs($this->admin)->getJson('Statistics::userSpace')->status());
	}

	public function testAlbumDrawerStorageEndpointsStay(): void
	{
		$this->assertOk($this->actingAs($this->userMayUpload1)->getJsonWithData('Statistics::totalAlbumSpace', ['album_id' => $this->album1->id]));
		$this->assertOk($this->actingAs($this->userMayUpload1)->getJsonWithData('Statistics::sizeVariantSpace', ['album_id' => $this->album1->id]));
		$this->assertOk($this->actingAs($this->admin)->getJson('Statistics::albumSpace'));
	}

	private function isRegistered(string $uri): bool
	{
		return collect(Route::getRoutes()->getRoutes())->contains(fn (\Illuminate\Routing\Route $route) => $route->uri() === $uri);
	}
}
