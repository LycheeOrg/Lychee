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

namespace Tests\Webshop\Purchasables;

use App\Enum\PurchasableLicenseType;
use App\Models\PixelSize;
use App\Models\PrintSize;
use App\Models\Purchasable;
use App\Models\PurchasablePixelSize;
use App\Models\PurchasablePrintSize;
use Tests\Feature_v2\Base\BaseApiWithDataTest;
use Tests\Traits\RequirePro;

/**
 * Test class for CatalogueSizesController.
 *
 * Tests the customer-facing endpoint for listing active print and pixel
 * sizes with prices assigned to a purchasable.
 */
class CatalogueSizesControllerTest extends BaseApiWithDataTest
{
	use RequirePro;

	private Purchasable $purchasable;

	public function setUp(): void
	{
		parent::setUp();
		$this->requirePro();

		$this->purchasable = Purchasable::factory()
			->forPhoto($this->photo1->id, $this->album1->id)
			->withPrices()
			->create();
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

	public function testReturnsEmptySizesWhenNoneAssigned(): void
	{
		$response = $this->actingAs($this->userMayUpload1)
			->getJson("Shop/Catalogue/Purchasable/{$this->purchasable->id}/Sizes");

		$this->assertOk($response);
		$response->assertJson(['print_sizes' => [], 'pixel_sizes' => []]);
	}

	public function testReturnsActivePrintSizes(): void
	{
		$active_print = PrintSize::factory()->create(['is_active' => true]);
		$inactive_print = PrintSize::factory()->create(['is_active' => false]);

		PurchasablePrintSize::factory()->create([
			'purchasable_id' => $this->purchasable->id,
			'print_size_id' => $active_print->id,
		]);
		PurchasablePrintSize::factory()->create([
			'purchasable_id' => $this->purchasable->id,
			'print_size_id' => $inactive_print->id,
		]);

		$response = $this->actingAs($this->userMayUpload1)
			->getJson("Shop/Catalogue/Purchasable/{$this->purchasable->id}/Sizes");

		$this->assertOk($response);

		$data = $response->json();
		$this->assertCount(1, $data['print_sizes']);
		$this->assertCount(0, $data['pixel_sizes']);
	}

	public function testReturnsActivePixelSizes(): void
	{
		$active_pixel = PixelSize::factory()->create(['is_active' => true]);
		$inactive_pixel = PixelSize::factory()->create(['is_active' => false]);

		PurchasablePixelSize::factory()->create([
			'purchasable_id' => $this->purchasable->id,
			'pixel_size_id' => $active_pixel->id,
			'license_type' => PurchasableLicenseType::PERSONAL,
		]);
		PurchasablePixelSize::factory()->create([
			'purchasable_id' => $this->purchasable->id,
			'pixel_size_id' => $inactive_pixel->id,
			'license_type' => PurchasableLicenseType::PERSONAL,
		]);

		$response = $this->actingAs($this->userMayUpload1)
			->getJson("Shop/Catalogue/Purchasable/{$this->purchasable->id}/Sizes");

		$this->assertOk($response);

		$data = $response->json();
		$this->assertCount(0, $data['print_sizes']);
		$this->assertCount(1, $data['pixel_sizes']);
	}

	public function testReturnsBothPrintAndPixelSizes(): void
	{
		$print = PrintSize::factory()->create(['is_active' => true]);
		$pixel = PixelSize::factory()->create(['is_active' => true]);

		PurchasablePrintSize::factory()->create([
			'purchasable_id' => $this->purchasable->id,
			'print_size_id' => $print->id,
		]);
		PurchasablePixelSize::factory()->create([
			'purchasable_id' => $this->purchasable->id,
			'pixel_size_id' => $pixel->id,
			'license_type' => PurchasableLicenseType::PERSONAL,
		]);

		$response = $this->actingAs($this->userMayUpload1)
			->getJson("Shop/Catalogue/Purchasable/{$this->purchasable->id}/Sizes");

		$this->assertOk($response);

		$data = $response->json();
		$this->assertCount(1, $data['print_sizes']);
		$this->assertCount(1, $data['pixel_sizes']);
		$response->assertJsonStructure([
			'print_sizes' => [['print_size_id', 'price_cents']],
			'pixel_sizes' => [['pixel_size_id', 'price_cents', 'license_type']],
		]);
	}

	public function testReturnsFourOhFourForUnknownPurchasable(): void
	{
		$response = $this->actingAs($this->userMayUpload1)
			->getJson('Shop/Catalogue/Purchasable/99999/Sizes');

		$this->assertNotFound($response);
	}
}
