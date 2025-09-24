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
 * Test class for ShopManagementController.
 *
 * This class tests the admin functionality for managing purchasable items:
 * - Creating purchasable photos
 * - Creating purchasable albums
 * - Updating purchasable prices
 * - Deleting purchasable items
 *
 * All endpoints require admin privileges (owner_id configuration).
 */
class ShopManagementControllerTest extends BaseApiWithDataTest
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
	 * Test creating a purchasable photo with valid data.
	 *
	 * @return void
	 */
	public function testSetPhotoPurchasableSuccess(): void
	{
		$response = $this->actingAs($this->admin)->postJson('Shop/Management/Purchasable/Photo', [
			'photo_ids' => [$this->photo1->id],
			'album_id' => $this->album1->id,
			'description' => 'Beautiful landscape photo',
			'note' => 'High quality print available',
			'prices' => [
				[
					'size_variant_type' => 'medium',
					'license_type' => 'personal',
					'price' => 1999, // $19.99 in cents
				],
				[
					'size_variant_type' => 'original',
					'license_type' => 'commercial',
					'price' => 4999, // $49.99 in cents
				],
			],
		]);

		$this->assertOk($response);
		$response->assertJsonStructure([
			'*' => [
				'purchasable_id',
				'album_id',
				'photo_id',
				'prices',
				'owner_notes',
				'description',
				'is_active',
			],
		]);

		// Verify the purchasable was created in the database
		$this->assertDatabaseHas('purchasables', [
			'photo_id' => $this->photo1->id,
			'album_id' => $this->album1->id,
			'description' => 'Beautiful landscape photo',
			'owner_notes' => 'High quality print available',
			'is_active' => true,
		]);

		// Verify prices were created
		$purchasable = Purchasable::where('photo_id', $this->photo1->id)->first();
		$this->assertNotNull($purchasable);
		$this->assertCount(2, $purchasable->prices);
	}

	/**
	 * Test creating purchasable photos with multiple photos.
	 *
	 * @return void
	 */
	public function testSetMultiplePhotosPurchasable(): void
	{
		$response = $this->actingAs($this->admin)->postJson('Shop/Management/Purchasable/Photo', [
			'photo_ids' => [$this->photo1->id, $this->photo1b->id],
			'album_id' => $this->album1->id,
			'description' => 'Premium photo collection',
			'note' => 'Limited time offer',
			'prices' => [
				[
					'size_variant_type' => 'medium',
					'license_type' => 'personal',
					'price' => 999,
				],
			],
		]);

		$this->assertOk($response);
		$response->assertJsonCount(2); // Should return 2 purchasable resources

		// Verify both photos have purchasables
		$this->assertDatabaseHas('purchasables', ['photo_id' => $this->photo1->id]);
		$this->assertDatabaseHas('purchasables', ['photo_id' => $this->photo1b->id]);
	}

	/**
	 * Test creating a purchasable album.
	 *
	 * @return void
	 */
	public function testSetAlbumPurchasableSuccess(): void
	{
		$response = $this->actingAs($this->admin)->postJson('Shop/Management/Purchasable/Album', [
			'album_ids' => [$this->album1->id],
			'description' => 'Complete photo album',
			'note' => 'All photos in high resolution',
			'applies_to_subalbums' => false,
			'prices' => [
				[
					'size_variant_type' => 'original',
					'license_type' => 'personal',
					'price' => 9999, // $99.99 in cents
				],
			],
		]);

		$this->assertOk($response);
		$response->assertJsonStructure([
			'*' => [
				'purchasable_id',
				'album_id',
				'photo_id',
				'prices',
				'owner_notes',
				'description',
				'is_active',
			],
		]);

		// Verify the purchasable was created
		$this->assertDatabaseHas('purchasables', [
			'album_id' => $this->album1->id,
			'photo_id' => null,
			'description' => 'Complete photo album',
			'owner_notes' => 'All photos in high resolution',
			'is_active' => true,
		]);
	}

	/**
	 * Test creating purchasable albums with multiple albums.
	 *
	 * @return void
	 */
	public function testSetMultipleAlbumsPurchasable(): void
	{
		$response = $this->actingAs($this->admin)->postJson('Shop/Management/Purchasable/Album', [
			'album_ids' => [$this->album1->id, $this->album2->id],
			'description' => 'Bundle offer',
			'note' => 'Two albums for the price of one',
			'applies_to_subalbums' => false,
			'prices' => [
				[
					'size_variant_type' => 'original',
					'license_type' => 'commercial',
					'price' => 14999,
				],
			],
		]);

		$this->assertOk($response);
		$response->assertJsonCount(2);

		// Verify both albums have purchasables
		$this->assertDatabaseHas('purchasables', [
			'album_id' => $this->album1->id,
			'photo_id' => null,
		]);
		$this->assertDatabaseHas('purchasables', [
			'album_id' => $this->album2->id,
			'photo_id' => null,
		]);
	}

	/**
	 * Test updating purchasable prices.
	 *
	 * @return void
	 */
	public function testUpdatePurchasablePricesSuccess(): void
	{
		// First create a purchasable
		$purchasable = Purchasable::factory()->create([
			'photo_id' => $this->photo1->id,
			'album_id' => $this->album1->id,
			'description' => 'Original description',
			'owner_notes' => 'Original notes',
		]);

		$response = $this->actingAs($this->admin)->putJson('Shop/Management/Purchasable/Price', [
			'purchasable_id' => $purchasable->id,
			'description' => 'Updated description',
			'note' => 'Updated notes',
			'prices' => [
				[
					'size_variant_type' => 'medium',
					'license_type' => 'personal',
					'price' => 2999,
				],
				[
					'size_variant_type' => 'full',
					'license_type' => 'extended',
					'price' => 7999,
				],
			],
		]);

		$this->assertOk($response);
		$response->assertJsonStructure([
			'purchasable_id',
			'album_id',
			'photo_id',
			'prices',
			'owner_notes',
			'description',
			'is_active',
		]);

		// Verify the description and notes were updated
		$purchasable->refresh();
		$this->assertEquals('Updated description', $purchasable->description);
		$this->assertEquals('Updated notes', $purchasable->owner_notes);
		$this->assertCount(2, $purchasable->prices);
	}

	/**
	 * Test deleting purchasable items.
	 *
	 * @return void
	 */
	public function testDeletePurchasablesSuccess(): void
	{
		// Create multiple purchasables
		$purchasable1 = Purchasable::factory()->create([
			'photo_id' => $this->photo1->id,
			'album_id' => $this->album1->id,
		]);
		$purchasable2 = Purchasable::factory()->create([
			'photo_id' => $this->photo2->id,
			'album_id' => $this->album2->id,
		]);

		$response = $this->actingAs($this->admin)->deleteJson('Shop/Management/Purchasables', [
			'purchasable_ids' => [$purchasable1->id, $purchasable2->id],
		]);

		$this->assertNoContent($response);

		// Verify the purchasables were deleted
		$this->assertDatabaseMissing('purchasables', ['id' => $purchasable1->id]);
		$this->assertDatabaseMissing('purchasables', ['id' => $purchasable2->id]);
	}

	/**
	 * Test that non-admin users cannot create purchasables.
	 *
	 * @return void
	 */
	public function testSetPhotoPurchasableUnauthorized(): void
	{
		$response = $this->actingAs($this->userMayUpload1)->postJson('Shop/Management/Purchasable/Photo', [
			'photo_ids' => [$this->photo1->id],
			'album_id' => $this->album1->id,
			'description' => 'Test description',
			'note' => 'Test notes',
			'prices' => [
				[
					'size_variant_type' => 'medium',
					'license_type' => 'personal',
					'price' => 1999,
				],
			],
		]);

		$this->assertForbidden($response);
	}

	/**
	 * Test that guests cannot create purchasables.
	 *
	 * @return void
	 */
	public function testSetPhotoPurchasableUnauthenticated(): void
	{
		$response = $this->postJson('Shop/Management/Purchasable/Photo', [
			'photo_ids' => [$this->photo1->id],
			'album_id' => $this->album1->id,
			'description' => 'Test description',
			'note' => 'Test notes',
			'prices' => [
				[
					'size_variant_type' => 'medium',
					'license_type' => 'personal',
					'price' => 1999,
				],
			],
		]);

		$this->assertUnauthorized($response);
	}

	/**
	 * Test validation errors for creating purchasable photos.
	 *
	 * @return void
	 */
	public function testSetPhotoPurchasableValidationErrors(): void
	{
		// Test missing required fields
		$response = $this->actingAs($this->admin)->postJson('Shop/Management/Purchasable/Photo', []);

		$this->assertUnprocessable($response);
		$response->assertJsonValidationErrors(['photo_ids', 'album_id', 'prices']);

		// Test invalid price (too high)
		$response = $this->actingAs($this->admin)->postJson('Shop/Management/Purchasable/Photo', [
			'photo_ids' => [$this->photo1->id],
			'album_id' => $this->album1->id,
			'description' => 'Test',
			'note' => 'Test',
			'prices' => [
				[
					'size_variant_type' => 'medium',
					'license_type' => 'personal',
					'price' => 1000001, // Over $10,000 limit
				],
			],
		]);

		$this->assertUnprocessable($response);
		$response->assertJsonValidationErrors(['prices.0.price']);

		// Test invalid enum values
		$response = $this->actingAs($this->admin)->postJson('Shop/Management/Purchasable/Photo', [
			'photo_ids' => [$this->photo1->id],
			'album_id' => $this->album1->id,
			'description' => 'Test',
			'note' => 'Test',
			'prices' => [
				[
					'size_variant_type' => 'invalid_size',
					'license_type' => 'invalid_license',
					'price' => 1999,
				],
			],
		]);

		$this->assertUnprocessable($response);
		$response->assertJsonValidationErrors(['prices.0.size_variant_type', 'prices.0.license_type']);
	}

	/**
	 * Test validation errors for creating purchasable albums.
	 *
	 * @return void
	 */
	public function testSetAlbumPurchasableValidationErrors(): void
	{
		// Test missing required fields
		$response = $this->actingAs($this->admin)->postJson('Shop/Management/Purchasable/Album', []);

		$this->assertUnprocessable($response);
		$response->assertJsonValidationErrors(['album_ids', 'prices', 'applies_to_subalbums']);

		// Test invalid album ID
		$response = $this->actingAs($this->admin)->postJson('Shop/Management/Purchasable/Album', [
			'album_ids' => ['invalid-id'],
			'description' => 'Test',
			'note' => 'Test',
			'applies_to_subalbums' => false,
			'prices' => [
				[
					'size_variant_type' => 'original',
					'license_type' => 'personal',
					'price' => 9999,
				],
			],
		]);

		$this->assertUnprocessable($response);
		$response->assertJsonValidationErrors(['album_ids.0']);
	}

	/**
	 * Test validation errors for updating purchasable prices.
	 *
	 * @return void
	 */
	public function testUpdatePurchasablePricesValidationErrors(): void
	{
		// Test missing required fields
		$response = $this->actingAs($this->admin)->putJson('Shop/Management/Purchasable/Price', []);

		$this->assertUnprocessable($response);
		$response->assertJsonValidationErrors(['purchasable_id', 'prices']);

		// Test invalid purchasable ID
		$response = $this->actingAs($this->admin)->putJson('Shop/Management/Purchasable/Price', [
			'purchasable_id' => 99999, // Non-existent ID
			'description' => 'Test',
			'note' => 'Test',
			'prices' => [
				[
					'size_variant_type' => 'medium',
					'license_type' => 'personal',
					'price' => 1999,
				],
			],
		]);

		$this->assertUnprocessable($response);
		$response->assertJsonValidationErrors(['purchasable_id']);
	}

	/**
	 * Test that creating a purchasable replaces existing ones for the same photo.
	 *
	 * @return void
	 */
	public function testSetPhotoPurchasableReplacesExisting(): void
	{
		// Create initial purchasable
		$original_purchasable = Purchasable::factory()->create([
			'photo_id' => $this->photo1->id,
			'album_id' => $this->album1->id,
			'description' => 'Original',
		]);

		$original_id = $original_purchasable->id;

		// Create new purchasable for the same photo
		$response = $this->actingAs($this->admin)->postJson('Shop/Management/Purchasable/Photo', [
			'photo_ids' => [$this->photo1->id],
			'album_id' => $this->album1->id,
			'description' => 'New description',
			'note' => 'New notes',
			'prices' => [
				[
					'size_variant_type' => 'medium',
					'license_type' => 'personal',
					'price' => 2999,
				],
			],
		]);

		$this->assertOk($response);

		// Verify the original purchasable was removed
		$this->assertDatabaseMissing('purchasables', ['id' => $original_id]);

		// Verify the new purchasable exists
		$this->assertDatabaseHas('purchasables', [
			'photo_id' => $this->photo1->id,
			'description' => 'New description',
		]);
	}

	/**
	 * Test that creating a purchasable album replaces existing ones for the same album.
	 *
	 * @return void
	 */
	public function testSetAlbumPurchasableReplacesExisting(): void
	{
		// Create initial purchasable
		$original_purchasable = Purchasable::factory()->create([
			'album_id' => $this->album1->id,
			'photo_id' => null,
			'description' => 'Original album',
		]);

		$original_id = $original_purchasable->id;

		// Create new purchasable for the same album
		$response = $this->actingAs($this->admin)->postJson('Shop/Management/Purchasable/Album', [
			'album_ids' => [$this->album1->id],
			'description' => 'New album description',
			'note' => 'New album notes',
			'applies_to_subalbums' => false,
			'prices' => [
				[
					'size_variant_type' => 'original',
					'license_type' => 'commercial',
					'price' => 14999,
				],
			],
		]);

		$this->assertOk($response);

		// Verify the original purchasable was removed
		$this->assertDatabaseMissing('purchasables', ['id' => $original_id]);

		// Verify the new purchasable exists
		$this->assertDatabaseHas('purchasables', [
			'album_id' => $this->album1->id,
			'photo_id' => null,
			'description' => 'New album description',
		]);
	}

	/**
	 * Test the options endpoint returns configuration settings.
	 */
	public function testOptions(): void
	{
		// Call the options endpoint with authentication
		$response = $this->actingAs($this->admin)->getJson('Shop/Management/Options');

		// Assert successful response
		$this->assertOk($response);

		// Assert response structure
		$response->assertJsonStructure([
			'currency',
			'default_price_cents',
			'default_license',
			'default_size',
		]);

		// Assert the response contains expected config values
		$responseData = $response->json();
		$this->assertIsString($responseData['currency']);
		$this->assertIsInt($responseData['default_price_cents']);
		$this->assertIsString($responseData['default_license']);
		$this->assertIsString($responseData['default_size']);
	}

	/**
	 * Test the list endpoint returns all purchasables.
	 */
	public function testList(): void
	{
		// Create some test purchasables first
		$this->actingAs($this->admin)->postJson('Shop/Management/Purchasable/Photo', [
			'photo_ids' => [$this->photo1->id],
			'album_id' => $this->album1->id,
			'description' => 'Test photo purchasable',
			'note' => 'Test notes',
			'prices' => [
				[
					'size_variant_type' => 'medium',
					'license_type' => 'personal',
					'price' => 1999,
				],
			],
		]);

		$this->actingAs($this->admin)->postJson('Shop/Management/Purchasable/Album', [
			'album_ids' => [$this->album2->id],
			'description' => 'Test album purchasable',
			'note' => 'Test album notes',
			'applies_to_subalbums' => false,
			'prices' => [
				[
					'size_variant_type' => 'original',
					'license_type' => 'commercial',
					'price' => 2999,
				],
			],
		]);

		// Call the list endpoint
		$response = $this->actingAs($this->admin)->getJson('Shop/Management/List');

		// Assert successful response
		$this->assertOk($response);

		// Assert response is an array
		$responseData = $response->json();
		$this->assertIsArray($responseData);

		// Assert we have the expected number of purchasables
		$this->assertGreaterThanOrEqual(2, count($responseData));

		// Assert response structure for first item
		if (count($responseData) > 0) {
			$this->assertArrayHasKey('purchasable_id', $responseData[0]);
			$this->assertArrayHasKey('album_id', $responseData[0]);
			$this->assertArrayHasKey('description', $responseData[0]);
			$this->assertArrayHasKey('is_active', $responseData[0]);
		}
	}

	/**
	 * Test the list endpoint with album filtering.
	 */
	public function testListWithAlbumFilter(): void
	{
		// Create purchasables in different albums
		$this->actingAs($this->admin)->postJson('Shop/Management/Purchasable/Photo', [
			'photo_ids' => [$this->photo1->id],
			'album_id' => $this->album1->id,
			'description' => 'Album1 photo',
			'note' => 'Test notes',
			'prices' => [
				[
					'size_variant_type' => 'medium',
					'license_type' => 'personal',
					'price' => 1999,
				],
			],
		]);

		$this->actingAs($this->admin)->postJson('Shop/Management/Purchasable/Album', [
			'album_ids' => [$this->album2->id],
			'description' => 'Album2 purchasable',
			'note' => 'Test notes',
			'applies_to_subalbums' => false,
			'prices' => [
				[
					'size_variant_type' => 'original',
					'license_type' => 'commercial',
					'price' => 2999,
				],
			],
		]);

		// Call the list endpoint with album filter
		$response = $this->actingAs($this->admin)->getJsonWithData('Shop/Management/List', [
			'album_ids' => [$this->album1->id],
		]);

		// Assert successful response
		$this->assertOk($response);

		$responseData = $response->json();
		$this->assertIsArray($responseData);

		// Assert all returned items belong to the filtered album
		foreach ($responseData as $item) {
			$this->assertEquals($this->album1->id, $item['album_id']);
		}
	}

	/**
	 * Test the options endpoint requires authentication.
	 */
	public function testOptionsRequiresAuth(): void
	{
		// Call the options endpoint without authentication
		$response = $this->getJson('Shop/Management/Options');

		// Assert unauthorized response
		$this->assertUnauthorized($response);
	}

	/**
	 * Test the list endpoint requires authentication.
	 */
	public function testListRequiresAuth(): void
	{
		// Call the list endpoint without authentication
		$response = $this->getJson('Shop/Management/List');

		// Assert unauthorized response
		$this->assertUnauthorized($response);
	}
}
