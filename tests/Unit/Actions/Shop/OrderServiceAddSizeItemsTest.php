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

namespace Tests\Unit\Actions\Shop;

use App\Actions\Shop\OrderService;
use App\Enum\PurchasableLicenseType;
use App\Exceptions\Shop\InvalidPurchaseOptionException;
use App\Exceptions\Shop\PhotoNotPurchasableException;
use App\Models\Order;
use App\Models\PixelSize;
use App\Models\PrintSize;
use App\Models\Purchasable;
use App\Models\PurchasablePixelSize;
use App\Models\PurchasablePrintSize;
use Tests\Feature_v2\Base\BaseApiWithDataTest;
use Tests\Traits\RequirePro;

/**
 * Unit tests for OrderService::addPrintPhotoToOrder and ::addPixelPhotoToOrder.
 *
 * Tests T-043-38: These methods create the correct OrderItem records.
 */
class OrderServiceAddSizeItemsTest extends BaseApiWithDataTest
{
	use RequirePro;

	private OrderService $order_service;
	private Order $order;
	private Purchasable $purchasable;
	private PrintSize $print_size;
	private PixelSize $pixel_size;

	public function setUp(): void
	{
		parent::setUp();
		$this->requirePro();

		$this->order_service = resolve(OrderService::class);

		$this->purchasable = Purchasable::factory()
			->forPhoto($this->photo1->id, $this->album1->id)
			->withPrices()
			->create();

		$this->print_size = PrintSize::factory()->create([
			'label' => '10x15 cm',
			'width' => 10,
			'height' => 15,
			'unit' => 'cm',
			'paper_type' => 'Matte',
			'is_active' => true,
		]);

		$this->pixel_size = PixelSize::factory()->create([
			'label' => '1920×1080',
			'width' => 1920,
			'height' => 1080,
			'is_active' => true,
		]);

		PurchasablePrintSize::factory()->withPrice(2500)->create([
			'purchasable_id' => $this->purchasable->id,
			'print_size_id' => $this->print_size->id,
		]);

		PurchasablePixelSize::factory()->withPrice(1200)->create([
			'purchasable_id' => $this->purchasable->id,
			'pixel_size_id' => $this->pixel_size->id,
			'license_type' => PurchasableLicenseType::PERSONAL,
		]);

		$this->order = Order::factory()->pending()->withEmail('test@example.com')->create();
	}

	public function tearDown(): void
	{
		PurchasablePrintSize::query()->delete();
		PurchasablePixelSize::query()->delete();
		PrintSize::query()->delete();
		PixelSize::query()->delete();
		$this->resetPro();
		parent::tearDown();
	}

	public function testAddPrintPhotoCreatesOrderItemWithIsPrintTrue(): void
	{
		$this->order_service->addPrintPhotoToOrder(
			$this->order,
			$this->photo1,
			$this->album1->id,
			$this->print_size,
		);

		$this->assertDatabaseHas('order_items', [
			'order_id' => $this->order->id,
			'photo_id' => $this->photo1->id,
			'is_print' => true,
			'print_size_id' => $this->print_size->id,
			'license_type' => PurchasableLicenseType::PRINT->value,
		]);
	}

	public function testAddPrintPhotoSnapshotsDimensions(): void
	{
		$this->order_service->addPrintPhotoToOrder(
			$this->order,
			$this->photo1,
			$this->album1->id,
			$this->print_size,
		);

		$this->assertDatabaseHas('order_items', [
			'order_id' => $this->order->id,
			'print_width' => 10,
			'print_height' => 15,
			'print_unit' => 'cm',
			'print_paper_type' => 'Matte',
		]);
	}

	public function testAddPrintPhotoStoresNotes(): void
	{
		$this->order_service->addPrintPhotoToOrder(
			$this->order,
			$this->photo1,
			$this->album1->id,
			$this->print_size,
			'Satin finish please',
		);

		$this->assertDatabaseHas('order_items', [
			'order_id' => $this->order->id,
			'item_notes' => 'Satin finish please',
		]);
	}

	public function testAddPrintPhotoThrowsWhenPhotoNotPurchasable(): void
	{
		$this->expectException(PhotoNotPurchasableException::class);

		$this->order_service->addPrintPhotoToOrder(
			$this->order,
			$this->photo2, // photo2 has no purchasable
			$this->album2->id,
			$this->print_size,
		);
	}

	public function testAddPrintPhotoThrowsWhenPrintSizeNotAssigned(): void
	{
		$unassigned_print_size = PrintSize::factory()->create();

		$this->expectException(InvalidPurchaseOptionException::class);

		$this->order_service->addPrintPhotoToOrder(
			$this->order,
			$this->photo1,
			$this->album1->id,
			$unassigned_print_size,
		);
	}

	public function testAddPixelPhotoCreatesOrderItemWithIsPrintFalse(): void
	{
		$this->order_service->addPixelPhotoToOrder(
			$this->order,
			$this->photo1,
			$this->album1->id,
			$this->pixel_size,
			PurchasableLicenseType::PERSONAL,
		);

		$this->assertDatabaseHas('order_items', [
			'order_id' => $this->order->id,
			'photo_id' => $this->photo1->id,
			'is_print' => false,
			'pixel_size_id' => $this->pixel_size->id,
		]);
	}

	public function testAddPixelPhotoSnapshotsDimensions(): void
	{
		$this->order_service->addPixelPhotoToOrder(
			$this->order,
			$this->photo1,
			$this->album1->id,
			$this->pixel_size,
			PurchasableLicenseType::PERSONAL,
		);

		$this->assertDatabaseHas('order_items', [
			'order_id' => $this->order->id,
			'pixel_width' => 1920,
			'pixel_height' => 1080,
		]);
	}

	public function testAddPixelPhotoThrowsWhenPhotoNotPurchasable(): void
	{
		$this->expectException(PhotoNotPurchasableException::class);

		$this->order_service->addPixelPhotoToOrder(
			$this->order,
			$this->photo2,
			$this->album2->id,
			$this->pixel_size,
			PurchasableLicenseType::PERSONAL,
		);
	}

	public function testAddPixelPhotoThrowsWhenPixelSizeNotAssigned(): void
	{
		$unassigned_pixel_size = PixelSize::factory()->create();

		$this->expectException(InvalidPurchaseOptionException::class);

		$this->order_service->addPixelPhotoToOrder(
			$this->order,
			$this->photo1,
			$this->album1->id,
			$unassigned_pixel_size,
			PurchasableLicenseType::PERSONAL,
		);
	}
}
