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

namespace Tests\Webshop;

use App\Enum\PaymentStatusType;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Purchasable;
use Illuminate\Support\Str;
use Tests\Feature_v2\Base\BaseApiWithDataTest;
use Tests\Traits\RequirePro;

/**
 * Test class for DeletePurchasedPhotoTest.
 *
 * This class tests that purchased photos and their containing albums can be
 * deleted while maintaining order integrity and content accessibility:
 * - Orders remain accessible after photo deletion
 * - Size variants remain accessible for downloaded content
 * - Order items maintain their references to deleted content
 *
 * The implementation uses nullable photo_id in size_variants and prevents
 * deletion of size variants that are referenced by order items.
 * See: database/migrations/2025_12_18_101617_preserve_size_variants_for_orders.php
 */
class DeletePurchasedPhotoTest extends BaseApiWithDataTest
{
	use RequirePro;

	private Purchasable $purchasable1;

	public function setUp(): void
	{
		parent::setUp();

		$this->requirePro();

		// Create purchasable items for testing
		$this->purchasable1 = Purchasable::factory()
			->forPhoto($this->photo1->id, $this->album1->id)
			->withPrices()
			->create();
	}

	public function tearDown(): void
	{
		$this->resetPro();
		parent::tearDown();
	}

	/**
	 * Test that a purchased photo can be deleted while preserving order access.
	 *
	 * Scenario:
	 * 1. Photo exists and is purchasable
	 * 2. Photo is purchased with a specific size variant
	 * 3. Order is completed and closed (delivered)
	 * 4. Admin deletes the photo
	 *
	 * Expected result:
	 * - Order still exists with CLOSED status
	 * - Order item still exists with size variant accessible
	 * - Size variant remains in database for customer downloads
	 *
	 * @return void
	 */
	public function testDeletePurchasedPhoto(): void
	{
		// Ensure photo has size variants loaded
		$this->photo1->load('size_variants');

		// Create a completed and closed order with the photo purchased
		$order = Order::factory()
			->withTransactionId(Str::uuid()->toString())
			->withEmail('customer@example.com')
			->closed()
			->withAmountCents(1999)
			->create();

		// Get the size variant for the photo
		$sizeVariant = $this->photo1->size_variants->getOriginal();
		$this->assertNotNull($sizeVariant, 'Size variant should exist for the photo');

		// Create order item linked to the size variant
		$orderItem = OrderItem::factory()
			->forOrder($order)
			->forPurchasable($this->purchasable1)
			->forPhoto($this->photo1)
			->originalSize()
			->create([
				'size_variant_id' => $sizeVariant->id,
			]);

		// Verify the order is properly set up
		$this->assertDatabaseHas('orders', [
			'id' => $order->id,
			'status' => PaymentStatusType::CLOSED->value,
		]);

		$this->assertDatabaseHas('order_items', [
			'id' => $orderItem->id,
			'order_id' => $order->id,
			'photo_id' => $this->photo1->id,
			'size_variant_id' => $sizeVariant->id,
		]);

		// Admin deletes the photo
		$response = $this->actingAs($this->admin)->deleteJson('Photo', [
			'photo_ids' => [$this->photo1->id],
			'from_id' => $this->album1->id,
		]);
		$this->assertNoContent($response);

		// Verify photo is deleted
		$this->assertDatabaseMissing('photos', [
			'id' => $this->photo1->id,
		]);

		// Verify order still exists with CLOSED status
		$this->assertDatabaseHas('orders', [
			'id' => $order->id,
			'status' => PaymentStatusType::CLOSED->value,
		]);

		// Verify order item still exists
		$this->assertDatabaseHas('order_items', [
			'id' => $orderItem->id,
			'order_id' => $order->id,
			'size_variant_id' => $sizeVariant->id,
		]);

		// Verify size variant is still accessible (soft-deleted or retained)
		$this->assertDatabaseHas('size_variants', [
			'id' => $sizeVariant->id,
		]);

		// Verify the order item can still access its content
		$orderItem->refresh();
		$this->assertNotNull($orderItem->size_variant_id, 'Order item should maintain size variant reference');
		$this->assertNotNull($orderItem->content_url, 'Order item should still have accessible content URL');
	}

	/**
	 * Test that an album containing purchased photos can be deleted while preserving order access.
	 *
	 * Scenario:
	 * 1. Photo exists in an album and is purchasable
	 * 2. Photo is purchased with a specific size variant
	 * 3. Order is completed and closed (delivered)
	 * 4. Admin deletes the album (which contains the photo)
	 *
	 * Expected result:
	 * - Order still exists with CLOSED status
	 * - Order item still exists with size variant accessible
	 * - Size variant remains in database for customer downloads
	 *
	 * @return void
	 */
	public function testDeleteAlbumContainingPurchasedPhoto(): void
	{
		// Ensure photo has size variants loaded
		$this->photo1->load('size_variants');

		// Create a completed and closed order with the photo purchased
		$order = Order::factory()
			->withTransactionId(Str::uuid()->toString())
			->withEmail('customer@example.com')
			->closed()
			->withAmountCents(1999)
			->create();

		// Get the size variant for the photo
		$sizeVariant = $this->photo1->size_variants->getOriginal();
		$this->assertNotNull($sizeVariant, 'Size variant should exist for the photo');

		// Create order item linked to the size variant
		$orderItem = OrderItem::factory()
			->forOrder($order)
			->forPurchasable($this->purchasable1)
			->forPhoto($this->photo1)
			->originalSize()
			->create([
				'size_variant_id' => $sizeVariant->id,
			]);

		// Verify the order is properly set up
		$this->assertDatabaseHas('orders', [
			'id' => $order->id,
			'status' => PaymentStatusType::CLOSED->value,
		]);

		$this->assertDatabaseHas('order_items', [
			'id' => $orderItem->id,
			'order_id' => $order->id,
			'photo_id' => $this->photo1->id,
			'size_variant_id' => $sizeVariant->id,
		]);

		// Admin deletes the album containing the photo
		$response = $this->actingAs($this->admin)->deleteJson('Album', [
			'album_ids' => [$this->album1->id],
		]);
		$this->assertNoContent($response);

		// Verify album is deleted
		$this->assertDatabaseMissing('albums', [
			'id' => $this->album1->id,
		]);

		// Verify photo is deleted (cascade from album deletion)
		$this->assertDatabaseMissing('photos', [
			'id' => $this->photo1->id,
		]);

		// Verify order still exists with CLOSED status
		$this->assertDatabaseHas('orders', [
			'id' => $order->id,
			'status' => PaymentStatusType::CLOSED->value,
		]);

		// Verify order item still exists
		$this->assertDatabaseHas('order_items', [
			'id' => $orderItem->id,
			'order_id' => $order->id,
			'size_variant_id' => $sizeVariant->id,
		]);

		// Verify size variant is still accessible (soft-deleted or retained)
		$this->assertDatabaseHas('size_variants', [
			'id' => $sizeVariant->id,
		]);

		// Verify the order item can still access its content
		$orderItem->refresh();
		$this->assertNotNull($orderItem->size_variant_id, 'Order item should maintain size variant reference');
		$this->assertNotNull($orderItem->content_url, 'Order item should still have accessible content URL');
	}
}