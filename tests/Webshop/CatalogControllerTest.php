<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
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

namespace Tests\Webshop;

use App\Models\Purchasable;
use Tests\Feature_v2\Base\BaseApiWithDataTest;
use Tests\Traits\RequireSE;

/**
 * Test class for CatalogController.
 *
 * This class tests the catalog functionality for viewing purchasable items:
 * - Getting catalog for albums with purchasable items
 * - Verifying album-level, photo-level, and children album purchasables
 * - Testing access permissions for catalog viewing
 *
 * The catalog should only show active purchasable items that the user has access to view.
 */
class CatalogControllerTest extends BaseApiWithDataTest
{
	use RequireSE;

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

	/**
	 * Test getting catalog for an album with no purchasable items.
	 *
	 * @return void
	 */
	public function testGetCatalogEmptyAlbum(): void
	{
		$response = $this->actingAs($this->userMayUpload1)->getJsonWithData('Shop', [
			'album_id' => $this->album1->id,
		]);

		$this->assertOk($response);
		$response->assertJsonStructure([
			'album_purchasable',
			'children_purchasables',
			'photo_purchasables',
		]);

		$response->assertJson([
			'album_purchasable' => null,
			'children_purchasables' => [],
			'photo_purchasables' => [],
		]);
	}

	/**
	 * Test getting catalog for an album with album-level purchasable.
	 *
	 * @return void
	 */
	public function testGetCatalogWithAlbumPurchasable(): void
	{
		// Create album-level purchasable
		$album_purchasable = Purchasable::factory()->create([
			'album_id' => $this->album1->id,
			'photo_id' => null,
			'description' => 'Complete album package',
			'owner_notes' => 'All photos included',
			'is_active' => true,
		]);

		$response = $this->actingAs($this->userMayUpload1)->getJsonWithData('Shop', [
			'album_id' => $this->album1->id,
		]);

		$this->assertOk($response);
		$response->assertJsonStructure([
			'album_purchasable' => [
				'purchasable_id',
				'album_id',
				'photo_id',
				'prices',
				'owner_notes',
				'description',
				'is_active',
			],
			'children_purchasables',
			'photo_purchasables',
		]);

		$response->assertJsonPath('album_purchasable.purchasable_id', $album_purchasable->id);
		$response->assertJsonPath('album_purchasable.album_id', $this->album1->id);
		$response->assertJsonPath('album_purchasable.photo_id', null);
		$response->assertJsonPath('album_purchasable.description', 'Complete album package');
	}

	/**
	 * Test getting catalog for an album with photo-level purchasables.
	 *
	 * @return void
	 */
	public function testGetCatalogWithPhotoPurchasables(): void
	{
		// Create photo-level purchasables
		$photo_purchasable1 = Purchasable::factory()->create([
			'album_id' => $this->album1->id,
			'photo_id' => $this->photo1->id,
			'description' => 'Premium landscape photo',
			'is_active' => true,
		]);

		$photo_purchasable2 = Purchasable::factory()->create([
			'album_id' => $this->album1->id,
			'photo_id' => $this->photo1b->id,
			'description' => 'Portrait masterpiece',
			'is_active' => true,
		]);

		$response = $this->actingAs($this->userMayUpload1)->getJsonWithData('Shop', [
			'album_id' => $this->album1->id,
		]);

		$this->assertOk($response);
		$response->assertJsonCount(2, 'photo_purchasables');

		// Check that both photo purchasables are included
		$photo_ids = array_column($response->json('photo_purchasables'), 'photo_id');
		$this->assertContains($this->photo1->id, $photo_ids);
		$this->assertContains($this->photo1b->id, $photo_ids);
	}

	/**
	 * Test getting catalog for an album with children album purchasables.
	 *
	 * @return void
	 */
	public function testGetCatalogWithChildrenPurchasables(): void
	{
		// Create purchasable for child album
		$child_purchasable = Purchasable::factory()->create([
			'album_id' => $this->subAlbum1->id,
			'photo_id' => null,
			'description' => 'Sub-album collection',
			'is_active' => true,
		]);

		$response = $this->actingAs($this->userMayUpload1)->getJsonWithData('Shop', [
			'album_id' => $this->album1->id,
		]);

		$this->assertOk($response);
		$response->assertJsonCount(1, 'children_purchasables');
		$response->assertJsonPath('children_purchasables.0.purchasable_id', $child_purchasable->id);
		$response->assertJsonPath('children_purchasables.0.album_id', $this->subAlbum1->id);
	}

	/**
	 * Test getting catalog with mixed purchasable types.
	 *
	 * @return void
	 */
	public function testGetCatalogWithMixedPurchasables(): void
	{
		// Create album-level purchasable
		$album_purchasable = Purchasable::factory()->create([
			'album_id' => $this->album1->id,
			'photo_id' => null,
			'description' => 'Complete album',
			'is_active' => true,
		]);

		// Create photo-level purchasable
		$photo_purchasable = Purchasable::factory()->create([
			'album_id' => $this->album1->id,
			'photo_id' => $this->photo1->id,
			'description' => 'Individual photo',
			'is_active' => true,
		]);

		// Create child album purchasable
		$child_purchasable = Purchasable::factory()->create([
			'album_id' => $this->subAlbum1->id,
			'photo_id' => null,
			'description' => 'Sub-album',
			'is_active' => true,
		]);

		$response = $this->actingAs($this->userMayUpload1)->getJsonWithData('Shop', [
			'album_id' => $this->album1->id,
		]);

		$this->assertOk($response);

		// Verify all types are present
		$this->assertNotNull($response->json('album_purchasable'));
		$response->assertJsonCount(1, 'photo_purchasables');
		$response->assertJsonCount(1, 'children_purchasables');

		$response->assertJsonPath('album_purchasable.purchasable_id', $album_purchasable->id);
		$response->assertJsonPath('photo_purchasables.0.purchasable_id', $photo_purchasable->id);
		$response->assertJsonPath('children_purchasables.0.purchasable_id', $child_purchasable->id);
	}

	/**
	 * Test that inactive purchasables are not included in catalog.
	 *
	 * @return void
	 */
	public function testGetCatalogExcludesInactivePurchasables(): void
	{
		// Create active purchasable
		$active_purchasable = Purchasable::factory()->create([
			'album_id' => $this->album1->id,
			'photo_id' => $this->photo1->id,
			'description' => 'Active photo',
			'is_active' => true,
		]);

		// Create inactive purchasable
		$inactive_purchasable = Purchasable::factory()->create([
			'album_id' => $this->album1->id,
			'photo_id' => $this->photo1b->id,
			'description' => 'Inactive photo',
			'is_active' => false,
		]);

		// Create inactive album purchasable
		Purchasable::factory()->create([
			'album_id' => $this->album1->id,
			'photo_id' => null,
			'description' => 'Inactive album',
			'is_active' => false,
		]);

		$response = $this->actingAs($this->userMayUpload1)->getJsonWithData('Shop', [
			'album_id' => $this->album1->id,
		]);

		$this->assertOk($response);

		// Should only include the active photo purchasable
		$this->assertNull($response->json('album_purchasable'));
		$response->assertJsonCount(1, 'photo_purchasables');
		$response->assertJsonPath('photo_purchasables.0.purchasable_id', $active_purchasable->id);
	}

	/**
	 * Test catalog access for shared album.
	 *
	 * @return void
	 */
	public function testGetCatalogForSharedAlbum(): void
	{
		// Create purchasable for album1 (owned by userMayUpload1)
		$purchasable = Purchasable::factory()->create([
			'album_id' => $this->album1->id,
			'photo_id' => null,
			'description' => 'Shared album package',
			'is_active' => true,
		]);

		// Access as userMayUpload2 who has permissions to album1
		$response = $this->actingAs($this->userMayUpload2)->getJsonWithData('Shop', [
			'album_id' => $this->album1->id,
		]);

		$this->assertOk($response);
		$response->assertJsonPath('album_purchasable.purchasable_id', $purchasable->id);
	}

	/**
	 * Test catalog access for public album.
	 *
	 * @return void
	 */
	public function testGetCatalogForPublicAlbum(): void
	{
		// Create purchasable for album4 (public album)
		$purchasable = Purchasable::factory()->create([
			'album_id' => $this->album4->id,
			'photo_id' => null,
			'description' => 'Public album package',
			'is_active' => true,
		]);

		// Access as guest user
		$response = $this->getJsonWithData('Shop', [
			'album_id' => $this->album4->id,
		]);

		$this->assertOk($response);
		$response->assertJsonPath('album_purchasable.purchasable_id', $purchasable->id);
	}

	/**
	 * Test catalog access denied for unauthorized album.
	 *
	 * @return void
	 */
	public function testGetCatalogUnauthorizedAlbum(): void
	{
		// Try to access album3 (owned by userNoUpload) as userMayUpload1
		$response = $this->actingAs($this->userMayUpload1)->getJsonWithData('Shop', [
			'album_id' => $this->album3->id,
		]);

		$this->assertForbidden($response);
	}

	/**
	 * Test catalog access denied for guest on private album.
	 *
	 * @return void
	 */
	public function testGetCatalogUnauthenticatedPrivateAlbum(): void
	{
		// Try to access album1 (private) as guest
		$response = $this->getJsonWithData('Shop', [
			'album_id' => $this->album1->id,
		]);

		$this->assertUnauthorized($response);
	}

	/**
	 * Test catalog validation errors.
	 *
	 * @return void
	 */
	public function testGetCatalogValidationErrors(): void
	{
		// Test missing album_id
		$response = $this->actingAs($this->userMayUpload1)->getJsonWithData('Shop');

		$this->assertUnprocessable($response);
		$response->assertJsonValidationErrors(['album_id']);

		// Test invalid album_id
		$response = $this->actingAs($this->userMayUpload1)->getJsonWithData('Shop', [
			'album_id' => 'invalid-id',
		]);

		$this->assertUnprocessable($response);
		$response->assertJsonValidationErrors(['album_id']);

		// Test non-existent album_id
		$response = $this->actingAs($this->userMayUpload1)->getJsonWithData('Shop', [
			'album_id' => 'abcdefghijklmnopqrstuvwxyz123456', // Valid format but doesn't exist
		]);

		$this->assertUnprocessable($response);
	}

	/**
	 * Test catalog with prices included in response.
	 *
	 * @return void
	 */
	public function testGetCatalogWithPrices(): void
	{
		// Create purchasable with prices
		$purchasable = Purchasable::factory()->withPrices()->create([
			'album_id' => $this->album1->id,
			'photo_id' => $this->photo1->id,
			'description' => 'Photo with pricing',
			'is_active' => true,
		]);

		$response = $this->actingAs($this->userMayUpload1)->getJsonWithData('Shop', [
			'album_id' => $this->album1->id,
		]);

		$this->assertOk($response);
		$response->assertJsonStructure([
			'photo_purchasables' => [
				'*' => [
					'purchasable_id',
					'album_id',
					'photo_id',
					'prices' => [
						'*' => [
							'size_variant',
							'license_type',
							'price',
						],
					],
					'owner_notes',
					'description',
					'is_active',
				],
			],
		]);

		$this->assertNotEmpty($response->json('photo_purchasables.0.prices'));
	}

	/**
	 * Test catalog response structure consistency.
	 *
	 * @return void
	 */
	public function testGetCatalogResponseStructure(): void
	{
		$response = $this->actingAs($this->userMayUpload1)->getJsonWithData('Shop', [
			'album_id' => $this->album1->id,
		]);

		$this->assertOk($response);

		// Verify the structure is always consistent even when empty
		$response->assertJsonStructure([
			'album_purchasable',
			'children_purchasables',
			'photo_purchasables',
		]);

		// Verify types
		$data = $response->json();
		$this->assertTrue(is_null($data['album_purchasable']) || is_array($data['album_purchasable']));
		$this->assertIsArray($data['children_purchasables']);
		$this->assertIsArray($data['photo_purchasables']);
	}
}
