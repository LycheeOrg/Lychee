<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

/**
 * We don't care for unhandled exceptions in tests.
 * It is the nature of a test to throw an exception.
 *
 * @noinspection PhpDocMissingThrowsInspection
 * @noinspection PhpUnhandledExceptionInspection
 */

namespace Tests\Feature_v2\LandingFeaturedItem;

use App\Models\Album;
use App\Models\LandingFeaturedItem;
use App\Models\Photo;
use Tests\Feature_v2\Base\BaseApiWithDataTest;
use Tests\Traits\RequiresEmptyLandingFeaturedItems;

class LandingFeaturedItemCrudTest extends BaseApiWithDataTest
{
	use RequiresEmptyLandingFeaturedItems;

	public function setUp(): void
	{
		parent::setUp();
		$this->setUpRequiresEmptyLandingFeaturedItems();
	}

	public function tearDown(): void
	{
		$this->tearDownRequiresEmptyLandingFeaturedItems();
		parent::tearDown();
	}

	public function testStoreRequiresAuthentication(): void
	{
		$album = Album::factory()->as_root()->owned_by($this->admin)->create();
		$response = $this->postJson('LandingFeaturedItem', ['item_type' => 'album', 'item_id' => $album->id]);
		$this->assertUnauthorized($response);
	}

	public function testStoreForbiddenForNonAdmin(): void
	{
		$album = Album::factory()->as_root()->owned_by($this->admin)->create();
		$response = $this->actingAs($this->userMayUpload1)->postJson('LandingFeaturedItem', ['item_type' => 'album', 'item_id' => $album->id]);
		$this->assertForbidden($response);
	}

	public function testStoreCreatesAlbumFeaturedItem(): void
	{
		$album = Album::factory()->as_root()->owned_by($this->admin)->create();

		$response = $this->actingAs($this->admin)->postJson('LandingFeaturedItem', [
			'item_type' => 'album',
			'item_id' => $album->id,
		]);
		$this->assertCreated($response);
		$response->assertJsonPath('item_type', 'album');
		$response->assertJsonPath('item_id', $album->id);
		$this->assertDatabaseCount('landing_featured_items', 1);
	}

	public function testStoreCreatesPhotoFeaturedItem(): void
	{
		$album = Album::factory()->as_root()->owned_by($this->admin)->create();
		$photo = Photo::factory()->owned_by($this->admin)->in($album)->create();

		$response = $this->actingAs($this->admin)->postJson('LandingFeaturedItem', [
			'item_type' => 'photo',
			'item_id' => $photo->id,
		]);
		$this->assertCreated($response);
		$response->assertJsonPath('item_type', 'photo');
		$response->assertJsonPath('item_id', $photo->id);
	}

	public function testStoreRejectsNonexistentItemId(): void
	{
		$response = $this->actingAs($this->admin)->postJson('LandingFeaturedItem', [
			'item_type' => 'album',
			'item_id' => 'nonexistent-id',
		]);
		$this->assertUnprocessable($response);
	}

	public function testStoreRejectsItemIdOfWrongType(): void
	{
		$album = Album::factory()->as_root()->owned_by($this->admin)->create();

		// Album ID submitted with item_type=photo must be rejected.
		$response = $this->actingAs($this->admin)->postJson('LandingFeaturedItem', [
			'item_type' => 'photo',
			'item_id' => $album->id,
		]);
		$this->assertUnprocessable($response);
	}

	public function testIndexRequiresAdmin(): void
	{
		$response = $this->actingAs($this->userMayUpload1)->getJson('LandingFeaturedItem');
		$this->assertForbidden($response);
	}

	public function testIndexReturnsAllItems(): void
	{
		LandingFeaturedItem::factory()->count(3)->create();

		$response = $this->actingAs($this->admin)->getJson('LandingFeaturedItem');
		$this->assertOk($response);
		$response->assertJsonCount(3, 'landing_featured_items');
	}

	public function testShowReturnsNotFoundForMissingItem(): void
	{
		$response = $this->actingAs($this->admin)->getJson('LandingFeaturedItem/nonexistent-id');
		$this->assertNotFound($response);
	}

	public function testPatchTogglesEnabled(): void
	{
		$item = LandingFeaturedItem::factory()->create(['enabled' => true]);

		$response = $this->actingAs($this->admin)->patchJson("LandingFeaturedItem/{$item->id}", ['enabled' => false]);
		$this->assertOk($response);
		$response->assertJsonPath('enabled', false);
	}

	public function testDestroyRemovesItem(): void
	{
		$item = LandingFeaturedItem::factory()->create();

		$response = $this->actingAs($this->admin)->deleteJson("LandingFeaturedItem/{$item->id}");
		$this->assertNoContent($response);
		$this->assertDatabaseCount('landing_featured_items', 0);
	}

	public function testDestroyForbiddenForNonAdmin(): void
	{
		$item = LandingFeaturedItem::factory()->create();

		$response = $this->actingAs($this->userMayUpload1)->deleteJson("LandingFeaturedItem/{$item->id}");
		$this->assertForbidden($response);
	}
}
