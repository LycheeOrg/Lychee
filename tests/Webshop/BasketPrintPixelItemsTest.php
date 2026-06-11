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

use App\Enum\PurchasableLicenseType;
use App\Models\PixelSize;
use App\Models\PrintSize;
use App\Models\Purchasable;
use App\Models\PurchasablePixelSize;
use App\Models\PurchasablePrintSize;
use Tests\Feature_v2\Base\BaseApiWithDataTest;
use Tests\Traits\RequirePro;

/**
 * Test class for print and pixel item basket operations.
 *
 * Tests T-043-31: Adding print/pixel items to the basket via
 * POST /Shop/Basket/Print and POST /Shop/Basket/Pixel.
 */
class BasketPrintPixelItemsTest extends BaseApiWithDataTest
{
	use RequirePro;

	private Purchasable $purchasable;
	private PrintSize $print_size;
	private PixelSize $pixel_size;

	public function setUp(): void
	{
		parent::setUp();
		$this->requirePro();

		$this->purchasable = Purchasable::factory()
			->forPhoto($this->photo1->id, $this->album1->id)
			->withPrices()
			->create();

		$this->print_size = PrintSize::factory()->create(['is_active' => true]);
		$this->pixel_size = PixelSize::factory()->create(['is_active' => true]);

		// Assign sizes with prices to the purchasable
		PurchasablePrintSize::factory()->withPrice(2500)->create([
			'purchasable_id' => $this->purchasable->id,
			'print_size_id' => $this->print_size->id,
		]);
		PurchasablePixelSize::factory()->withPrice(1200)->create([
			'purchasable_id' => $this->purchasable->id,
			'pixel_size_id' => $this->pixel_size->id,
			'license_type' => PurchasableLicenseType::PERSONAL,
		]);
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

	public function testAddPrintItemToBasket(): void
	{
		$response = $this->getJson('Shop/Basket/');
		$order_id = $response->getCookie('basket_id')->getValue();

		$response = $this->withCookie('basket_id', $order_id)->postJson('Shop/Basket/Print', [
			'photo_id' => $this->photo1->id,
			'album_id' => $this->album1->id,
			'print_size_id' => $this->print_size->id,
		]);

		$this->assertCreated($response);
		$response->assertJsonStructure([
			'id',
			'status',
			'items',
		]);

		$items = $response->json('items');
		$this->assertCount(1, $items);
		$this->assertTrue($items[0]['is_print']);
		$this->assertEquals($this->print_size->id, $items[0]['print_size_id']);
	}

	public function testAddPixelItemToBasket(): void
	{
		$response = $this->getJson('Shop/Basket/');
		$order_id = $response->getCookie('basket_id')->getValue();

		$response = $this->withCookie('basket_id', $order_id)->postJson('Shop/Basket/Pixel', [
			'photo_id' => $this->photo1->id,
			'album_id' => $this->album1->id,
			'pixel_size_id' => $this->pixel_size->id,
			'license_type' => PurchasableLicenseType::PERSONAL->value,
		]);

		$this->assertCreated($response);

		$items = $response->json('items');
		$this->assertCount(1, $items);
		$this->assertFalse($items[0]['is_print']);
		$this->assertEquals($this->pixel_size->id, $items[0]['pixel_size_id']);
	}

	public function testAddPrintItemRequiresPrintSizeId(): void
	{
		$response = $this->getJson('Shop/Basket/');
		$order_id = $response->getCookie('basket_id')->getValue();

		$response = $this->withCookie('basket_id', $order_id)->postJson('Shop/Basket/Print', [
			'photo_id' => $this->photo1->id,
			'album_id' => $this->album1->id,
		]);

		$this->assertUnprocessable($response);
		$response->assertJsonValidationErrors(['print_size_id']);
	}

	public function testAddPixelItemRequiresPixelSizeId(): void
	{
		$response = $this->getJson('Shop/Basket/');
		$order_id = $response->getCookie('basket_id')->getValue();

		$response = $this->withCookie('basket_id', $order_id)->postJson('Shop/Basket/Pixel', [
			'photo_id' => $this->photo1->id,
			'album_id' => $this->album1->id,
		]);

		$this->assertUnprocessable($response);
		$response->assertJsonValidationErrors(['pixel_size_id']);
	}

	public function testAddPrintItemRejectsInvalidPrintSizeId(): void
	{
		$response = $this->getJson('Shop/Basket/');
		$order_id = $response->getCookie('basket_id')->getValue();

		$response = $this->withCookie('basket_id', $order_id)->postJson('Shop/Basket/Print', [
			'photo_id' => $this->photo1->id,
			'album_id' => $this->album1->id,
			'print_size_id' => 99999,
		]);

		$this->assertNotFound($response);
	}

	public function testAddPrintItemWithNotesStoresNotes(): void
	{
		$response = $this->getJson('Shop/Basket/');
		$order_id = $response->getCookie('basket_id')->getValue();

		$response = $this->withCookie('basket_id', $order_id)->postJson('Shop/Basket/Print', [
			'photo_id' => $this->photo1->id,
			'album_id' => $this->album1->id,
			'print_size_id' => $this->print_size->id,
			'notes' => 'Please use satin finish',
		]);

		$this->assertCreated($response);
		$items = $response->json('items');
		$this->assertCount(1, $items);
		$this->assertEquals('Please use satin finish', $items[0]['item_notes']);
	}
}
