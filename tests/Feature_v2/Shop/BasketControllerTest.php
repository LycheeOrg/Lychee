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

namespace Tests\Feature_v2\Shop;

use App\Enum\OmnipayProviderType;
use App\Enum\PaymentStatusType;
use App\Enum\PurchasableLicenseType;
use App\Enum\PurchasableSizeVariantType;
use App\Models\Order;
use App\Models\Purchasable;
use Illuminate\Support\Facades\Session;
use Tests\Feature_v2\Base\BaseApiWithDataTest;
use Tests\Traits\RequireSE;

/**
 * Test class for BasketController.
 *
 * This class tests the basket functionality for the shop:
 * - Adding photos to basket
 * - Adding albums to basket
 * - Removing items from basket
 * - Getting current basket
 * - Deleting entire basket
 *
 * The basket is essentially a pending order that can be modified.
 */
class BasketControllerTest extends BaseApiWithDataTest
{
	use RequireSE;

	private Purchasable $purchasable1;
	private Purchasable $purchasable2;

	public function setUp(): void
	{
		parent::setUp();

		$this->requireSe();

		// Create purchasable items for testing
		$this->purchasable1 = Purchasable::factory()
			->forPhoto($this->photo1->id, $this->album1->id)
			->withPrices()
			->create();

		$this->purchasable2 = Purchasable::factory()
			->forPhoto($this->photo2->id, $this->album2->id)
			->withPrices()
			->create();
	}

	public function tearDown(): void
	{
		$this->resetSe();
		parent::tearDown();
	}

	/**
	 * Test getting an empty basket when no basket exists.
	 *
	 * @return void
	 */
	public function testGetEmptyBasket(): void
	{
		$response = $this->getJson('Shop/Basket/');

		$this->assertOk($response);
		$response->assertJsonStructure([
			'id',
			'provider',
			'user_id',
			'email',
			'status',
			'amount',
			'paid_at',
			'created_at',
			'comment',
			'items',
		]);

		$response->assertJson([
			'status' => PaymentStatusType::PENDING->value,
			'user_id' => null,
			'items' => [],
		]);

		// Should create a new basket in session
		$this->assertNotNull(Session::get('basket_id'));
	}

	/**
	 * Test getting basket when user is authenticated.
	 *
	 * @return void
	 */
	public function testGetBasketWhenAuthenticated(): void
	{
		$response = $this->actingAs($this->userMayUpload1)->getJson('Shop/Basket/');

		$this->assertOk($response);
		$response->assertJson([
			'status' => PaymentStatusType::PENDING->value,
			'user_id' => $this->userMayUpload1->id,
			'items' => [],
		]);
	}

	/**
	 * Test adding a photo to the basket successfully.
	 *
	 * @return void
	 */
	public function testAddPhotoToBasketSuccess(): void
	{
		$response = $this->getJson('Shop/Basket/');
		$response = $this->postJson('Shop/Basket/Photo', [
			'photo_id' => $this->photo1->id,
			'album_id' => $this->album1->id,
			'size_variant' => PurchasableSizeVariantType::MEDIUM->value,
			'license_type' => PurchasableLicenseType::PERSONAL->value,
			'notes' => 'Test photo purchase',
		]);

		$this->assertCreated($response);

		$response->assertJsonStructure([
			'id',
			'status',
			'items' => [
				'*' => [
					'id',
					'order_id',
					'purchasable_id',
					'album_id',
					'photo_id',
					'title',
					'license_type',
					'price',
					'size_variant_type',
					'item_notes',
				],
			],
		]);

		$response->assertJson([
			'status' => PaymentStatusType::PENDING->value,
		]);

		// Check that one item was added
		$responseData = $response->json();
		$this->assertCount(1, $responseData['items']);
		$this->assertEquals($this->photo1->id, $responseData['items'][0]['photo_id']);
		$this->assertEquals($this->album1->id, $responseData['items'][0]['album_id']);
		$this->assertEquals(PurchasableLicenseType::PERSONAL->value, $responseData['items'][0]['license_type']);
		$this->assertEquals(PurchasableSizeVariantType::MEDIUM->value, $responseData['items'][0]['size_variant_type']);
		$this->assertEquals('Test photo purchase', $responseData['items'][0]['item_notes']);
	}

	/**
	 * Test adding a photo to basket with validation errors.
	 *
	 * @return void
	 */
	public function testAddPhotoToBasketValidationError(): void
	{
		$response = $this->getJson('Shop/Basket/');
		$response = $this->postJson('Shop/Basket/Photo', [
			'photo_id' => $this->photo1->id,
			'album_id' => $this->album1->id,
			'size_variant' => 'invalid-size',
			'license_type' => 'invalid-license',
		]);

		$this->assertUnprocessable($response);
		$response->assertJsonValidationErrors(['size_variant', 'license_type']);
	}

	/**
	 * Test adding a photo that doesn't exist.
	 *
	 * @return void
	 */
	public function testAddNonExistentPhotoToBasket(): void
	{
		$response = $this->getJson('Shop/Basket/');
		$response = $this->postJson('Shop/Basket/Photo', [
			'photo_id' => 'non-existent-id',
			'album_id' => $this->album1->id,
			'size_variant' => PurchasableSizeVariantType::MEDIUM->value,
			'license_type' => PurchasableLicenseType::PERSONAL->value,
		]);

		$this->assertNotFound($response);
	}

	/**
	 * Test adding an album to the basket successfully.
	 *
	 * @return void
	 */
	public function testAddAlbumToBasketSuccess(): void
	{
		$response = $this->getJson('Shop/Basket/');
		$response = $this->actingAs($this->userMayUpload1)->postJson('Shop/Basket/Album', [
			'album_id' => $this->album1->id,
			'size_variant' => PurchasableSizeVariantType::ORIGINAL->value,
			'license_type' => PurchasableLicenseType::COMMERCIAL->value,
			'notes' => 'Album purchase notes',
			'include_subalbums' => false,
		]);

		$this->assertCreated($response);

		$response->assertJsonStructure([
			'id',
			'status',
			'items',
		]);

		$response->assertJson([
			'status' => PaymentStatusType::PENDING->value,
		]);

		// The number of items should match the number of purchasable photos in the album
		$responseData = $response->json();
		$this->assertGreaterThan(0, count($responseData['items']));
	}

	/**
	 * Test adding an album with subalbums included.
	 *
	 * @return void
	 */
	public function testAddAlbumToBasketWithSubalbums(): void
	{
		$response = $this->getJson('Shop/Basket/');
		$response = $this->postJson('Shop/Basket/Album', [
			'album_id' => $this->album1->id,
			'size_variant' => PurchasableSizeVariantType::ORIGINAL->value,
			'license_type' => PurchasableLicenseType::COMMERCIAL->value,
			'include_subalbums' => true,
		]);

		$this->assertCreated($response);
		$response->assertJson([
			'status' => PaymentStatusType::PENDING->value,
		]);
	}

	/**
	 * Test adding album with validation errors.
	 *
	 * @return void
	 */
	public function testAddAlbumToBasketValidationError(): void
	{
		$response = $this->getJson('Shop/Basket/');
		$response = $this->postJson('Shop/Basket/Album', [
			'album_id' => $this->album1->id,
			'size_variant' => 'invalid-size',
			'license_type' => 'invalid-license',
		]);

		$this->assertUnprocessable($response);
		$response->assertJsonValidationErrors(['size_variant', 'license_type']);
	}

	/**
	 * Test adding album with validation errors.
	 *
	 * @return void
	 */
	public function testAddAlbumToBasketValidationNotFound(): void
	{
		$response = $this->getJson('Shop/Basket/');
		$response = $this->postJson('Shop/Basket/Album', [
			'album_id' => 'non-existent-album',
			'size_variant' => PurchasableSizeVariantType::ORIGINAL->value,
			'license_type' => PurchasableLicenseType::COMMERCIAL->value,
		]);

		$this->assertNotFound($response);
	}

	/**
	 * Test removing an item from the basket.
	 *
	 * @return void
	 */
	public function testRemoveItemFromBasket(): void
	{
		// First, add an item to the basket
		$response = $this->getJson('Shop/Basket/');
		$addResponse = $this->postJson('Shop/Basket/Photo', [
			'photo_id' => $this->photo1->id,
			'album_id' => $this->album1->id,
			'size_variant' => PurchasableSizeVariantType::MEDIUM->value,
			'license_type' => PurchasableLicenseType::PERSONAL->value,
		]);

		$this->assertCreated($addResponse);
		$addData = $addResponse->json();
		$itemId = $addData['items'][0]['id'];

		// Now remove the item
		$removeResponse = $this->deleteJson('Shop/Basket/item', [
			'item_id' => $itemId,
		]);

		$this->assertOk($removeResponse);
		$removeResponse->assertJson([
			'status' => PaymentStatusType::PENDING->value,
			'items' => [],
		]);
	}

	/**
	 * Test removing a non-existent item from basket.
	 *
	 * @return void
	 */
	public function testRemoveNonExistentItemFromBasket(): void
	{
		$response = $this->getJson('Shop/Basket/');
		$response = $this->deleteJson('Shop/Basket/item', [
			'item_id' => 99999,
		]);

		$this->assertUnauthorized($response);
	}

	/**
	 * Test deleting the entire basket.
	 *
	 * @return void
	 */
	public function testDeleteBasket(): void
	{
		$response = $this->getJson('Shop/Basket/');
		$this->postJson('Shop/Basket/Photo', [
			'photo_id' => $this->photo1->id,
			'album_id' => $this->album1->id,
			'size_variant' => PurchasableSizeVariantType::MEDIUM->value,
			'license_type' => PurchasableLicenseType::PERSONAL->value,
		]);

		// Store the basket ID before deletion
		$basketId = Session::get('basket_id');
		$this->assertNotNull($basketId);

		// Delete the basket
		$response = $this->deleteJson('Shop/Basket/');

		$this->assertNoContent($response);

		// Check that basket ID is removed from session
		$this->assertNull(Session::get('basket_id'));

		// Check that the order is deleted from database
		$this->assertDatabaseMissing('orders', ['id' => $basketId]);
	}

	/**
	 * Test deleting a basket that doesn't exist.
	 *
	 * @return void
	 */
	public function testDeleteNonExistentBasket(): void
	{
		// Try to delete without having a basket
		$response = $this->deleteJson('Shop/Basket/');

		// Should still return 200 as it's idempotent
		$this->assertNoContent($response);
	}

	/**
	 * Test that adding items to basket maintains session continuity.
	 *
	 * @return void
	 */
	public function testBasketSessionContinuity(): void
	{
		// Add first item
		$response = $this->getJson('Shop/Basket/');
		$response1 = $this->postJson('Shop/Basket/Photo', [
			'photo_id' => $this->photo1->id,
			'album_id' => $this->album1->id,
			'size_variant' => PurchasableSizeVariantType::MEDIUM->value,
			'license_type' => PurchasableLicenseType::PERSONAL->value,
		]);

		$this->assertCreated($response1);
		$basketId1 = $response1->json('id');
		$this->assertNotNull($basketId1);

		// Add second item - should use same basket
		$response2 = $this->postJson('Shop/Basket/Photo', [
			'photo_id' => $this->photo2->id,
			'album_id' => $this->album2->id,
			'size_variant' => PurchasableSizeVariantType::ORIGINAL->value,
			'license_type' => PurchasableLicenseType::COMMERCIAL->value,
		]);

		$this->assertCreated($response2);
		$basketId2 = $response2->json('id');
		$this->assertNotNull($basketId2);

		// Should be the same basket
		$this->assertEquals($basketId1, $basketId2);
		$this->assertCount(2, $response2->json('items'));
	}

	/**
	 * Test that authenticated users get their own basket.
	 *
	 * @return void
	 */
	public function testAuthenticatedUserBasket(): void
	{
		// Add item as authenticated user
		$response = $this->actingAs($this->userMayUpload1)->getJson('Shop/Basket/');
		$response = $this->actingAs($this->userMayUpload1)
			->postJson('Shop/Basket/Photo', [
				'photo_id' => $this->photo1->id,
				'album_id' => $this->album1->id,
				'size_variant' => PurchasableSizeVariantType::MEDIUM->value,
				'license_type' => PurchasableLicenseType::PERSONAL->value,
			]);

		$this->assertCreated($response);
		$response->assertJson([
			'user_id' => $this->userMayUpload1->id,
		]);

		// Verify basket is associated with the user
		$basketId = $response->json('id');
		$this->assertDatabaseHas('orders', [
			'id' => $basketId,
			'user_id' => $this->userMayUpload1->id,
			'status' => PaymentStatusType::PENDING->value,
		]);
	}

	/**
	 * Test basket operations with email field.
	 *
	 * @return void
	 */
	public function testBasketWithEmail(): void
	{
		$response = $this->getJson('Shop/Basket/');
		$response = $this->postJson('Shop/Basket/Photo', [
			'photo_id' => $this->photo1->id,
			'album_id' => $this->album1->id,
			'size_variant' => PurchasableSizeVariantType::MEDIUM->value,
			'license_type' => PurchasableLicenseType::PERSONAL->value,
			'email' => 'test@example.com',
		]);

		$this->assertCreated($response);

		// Email should not be set on the order until checkout
		$response->assertJson([
			'email' => '',
		]);
	}

	/**
	 * Test adding item with invalid email format.
	 *
	 * @return void
	 */
	public function testAddItemWithInvalidEmail(): void
	{
		$response = $this->getJson('Shop/Basket/');
		$response = $this->postJson('Shop/Basket/Photo', [
			'photo_id' => $this->photo1->id,
			'album_id' => $this->album1->id,
			'size_variant' => PurchasableSizeVariantType::MEDIUM->value,
			'license_type' => PurchasableLicenseType::PERSONAL->value,
			'email' => 'invalid-email',
		]);

		$this->assertUnprocessable($response);
		$response->assertJsonValidationErrors(['email']);
	}

	/**
	 * Test that only pending baskets can be modified.
	 *
	 * @return void
	 */
	public function testCannotModifyNonPendingBasket(): void
	{
		// Create a completed order manually
		$order = new Order([
			'transaction_id' => 'test-transaction-123',
			'provider' => OmnipayProviderType::DUMMY,
			'user_id' => null,
			'email' => 'test@example.com',
			'status' => PaymentStatusType::COMPLETED,
			'amount_cents' => resolve(\App\Services\MoneyService::class)->createFromCents(1999),
			'comment' => 'Test completed order',
		]);
		$order->save();

		// Try to add item to completed order by manually setting session
		Session::put('basket_id', $order->id);

		$response = $this->postJson('Shop/Basket/Photo', [
			'photo_id' => $this->photo1->id,
			'album_id' => $this->album1->id,
			'size_variant' => PurchasableSizeVariantType::MEDIUM->value,
			'license_type' => PurchasableLicenseType::PERSONAL->value,
		]);

		// Should fail because the order is not pending
		$this->assertForbidden($response);
	}

	/**
	 * Test adding notes to basket items.
	 *
	 * @return void
	 */
	public function testAddItemsWithNotes(): void
	{
		$notes = 'Special print instructions';

		$response = $this->getJson('Shop/Basket/');
		$response = $this->postJson('Shop/Basket/Photo', [
			'photo_id' => $this->photo1->id,
			'album_id' => $this->album1->id,
			'size_variant' => PurchasableSizeVariantType::MEDIUM->value,
			'license_type' => PurchasableLicenseType::PERSONAL->value,
			'notes' => $notes,
		]);

		$this->assertCreated($response);
		$response->assertJsonPath('items.0.item_notes', $notes);
	}

	/**
	 * Test adding notes with maximum length.
	 *
	 * @return void
	 */
	public function testAddItemWithTooLongNotes(): void
	{
		$longNotes = str_repeat('x', 1001); // Exceeds 1000 character limit

		$response = $this->getJson('Shop/Basket/');
		$response = $this->postJson('Shop/Basket/Photo', [
			'photo_id' => $this->photo1->id,
			'album_id' => $this->album1->id,
			'size_variant' => PurchasableSizeVariantType::MEDIUM->value,
			'license_type' => PurchasableLicenseType::PERSONAL->value,
			'notes' => $longNotes,
		]);

		$this->assertUnprocessable($response);
		$response->assertJsonValidationErrors(['notes']);
	}

	/**
	 * Test that basket total is calculated correctly.
	 *
	 * @return void
	 */
	public function testBasketTotalCalculation(): void
	{
		// Add multiple items and verify total
		$response = $this->getJson('Shop/Basket/');
		$this->postJson('Shop/Basket/Photo', [
			'photo_id' => $this->photo1->id,
			'album_id' => $this->album1->id,
			'size_variant' => PurchasableSizeVariantType::MEDIUM->value,
			'license_type' => PurchasableLicenseType::PERSONAL->value,
		]);

		$response = $this->postJson('Shop/Basket/Photo', [
			'photo_id' => $this->photo2->id,
			'album_id' => $this->album2->id,
			'size_variant' => PurchasableSizeVariantType::ORIGINAL->value,
			'license_type' => PurchasableLicenseType::COMMERCIAL->value,
		]);

		$this->assertCreated($response);

		// Amount should be greater than 0 and reflect the sum of all items
		$responseData = $response->json();
		$this->assertGreaterThan(0, $responseData['amount']);
		$this->assertCount(2, $responseData['items']);
	}
}
