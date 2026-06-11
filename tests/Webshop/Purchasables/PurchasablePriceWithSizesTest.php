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

use App\Models\PixelSize;
use App\Models\PrintSize;
use App\Models\Purchasable;
use Tests\Feature_v2\Base\BaseApiWithDataTest;
use Tests\Traits\RequirePro;

/**
 * Test class for updating purchasable prices with print and pixel size assignments.
 *
 * Tests T-043-26: Extending UpdatePurchasablePriceRequest + ShopManagementController sync.
 */
class PurchasablePriceWithSizesTest extends BaseApiWithDataTest
{
	use RequirePro;

	private Purchasable $purchasable;
	private PrintSize $print_size1;
	private PrintSize $print_size2;
	private PixelSize $pixel_size1;
	private PixelSize $pixel_size2;

	public function setUp(): void
	{
		parent::setUp();
		$this->requirePro();

		$this->purchasable = Purchasable::factory()
			->forPhoto($this->photo1->id, $this->album1->id)
			->withPrices()
			->create();

		$this->print_size1 = PrintSize::factory()->create(['is_active' => true]);
		$this->print_size2 = PrintSize::factory()->create(['is_active' => true]);
		$this->pixel_size1 = PixelSize::factory()->create(['is_active' => true]);
		$this->pixel_size2 = PixelSize::factory()->create(['is_active' => true]);
	}

	public function tearDown(): void
	{
		PrintSize::query()->delete();
		PixelSize::query()->delete();
		$this->resetPro();
		parent::tearDown();
	}

	public function testUpdateWithPrintSizesCreatesPrintAssignments(): void
	{
		$response = $this->actingAs($this->admin)->putJson('Shop/Management/Purchasable/Price', [
			'purchasable_id' => $this->purchasable->id,
			'description' => 'Photo with print',
			'note' => '',
			'prices' => [
				['size_variant_type' => 'medium', 'license_type' => 'personal', 'price' => 1999],
			],
			'print_sizes' => [
				['print_size_id' => $this->print_size1->id, 'price' => 2500],
				['print_size_id' => $this->print_size2->id, 'price' => 3500],
			],
			'pixel_sizes' => [],
		]);

		$this->assertOk($response);

		$this->assertDatabaseHas('purchasable_print_sizes', [
			'purchasable_id' => $this->purchasable->id,
			'print_size_id' => $this->print_size1->id,
		]);
		$this->assertDatabaseHas('purchasable_print_sizes', [
			'purchasable_id' => $this->purchasable->id,
			'print_size_id' => $this->print_size2->id,
		]);
	}

	public function testUpdateWithPixelSizesCreatesPixelAssignments(): void
	{
		$response = $this->actingAs($this->admin)->putJson('Shop/Management/Purchasable/Price', [
			'purchasable_id' => $this->purchasable->id,
			'description' => 'Photo with pixel',
			'note' => '',
			'prices' => [
				['size_variant_type' => 'medium', 'license_type' => 'personal', 'price' => 1999],
			],
			'print_sizes' => [],
			'pixel_sizes' => [
				['pixel_size_id' => $this->pixel_size1->id, 'license_type' => 'personal', 'price' => 1200],
				['pixel_size_id' => $this->pixel_size2->id, 'license_type' => 'personal', 'price' => 1800],
			],
		]);

		$this->assertOk($response);

		$this->assertDatabaseHas('purchasable_pixel_sizes', [
			'purchasable_id' => $this->purchasable->id,
			'pixel_size_id' => $this->pixel_size1->id,
		]);
		$this->assertDatabaseHas('purchasable_pixel_sizes', [
			'purchasable_id' => $this->purchasable->id,
			'pixel_size_id' => $this->pixel_size2->id,
		]);
	}

	public function testUpdateSyncReplacesPreviousPrintAssignments(): void
	{
		// Assign print_size1 first
		$this->actingAs($this->admin)->putJson('Shop/Management/Purchasable/Price', [
			'purchasable_id' => $this->purchasable->id,
			'description' => 'Initial',
			'note' => '',
			'prices' => [
				['size_variant_type' => 'medium', 'license_type' => 'personal', 'price' => 1999],
			],
			'print_sizes' => [
				['print_size_id' => $this->print_size1->id, 'price' => 2500],
			],
			'pixel_sizes' => [],
		]);

		// Re-assign with print_size2 only — print_size1 should be removed
		$response = $this->actingAs($this->admin)->putJson('Shop/Management/Purchasable/Price', [
			'purchasable_id' => $this->purchasable->id,
			'description' => 'Updated',
			'note' => '',
			'prices' => [
				['size_variant_type' => 'medium', 'license_type' => 'personal', 'price' => 1999],
			],
			'print_sizes' => [
				['print_size_id' => $this->print_size2->id, 'price' => 3500],
			],
			'pixel_sizes' => [],
		]);

		$this->assertOk($response);

		$this->assertDatabaseMissing('purchasable_print_sizes', [
			'purchasable_id' => $this->purchasable->id,
			'print_size_id' => $this->print_size1->id,
		]);
		$this->assertDatabaseHas('purchasable_print_sizes', [
			'purchasable_id' => $this->purchasable->id,
			'print_size_id' => $this->print_size2->id,
		]);
	}

	public function testUpdateResponseIncludesPrintAndPixelSizes(): void
	{
		$response = $this->actingAs($this->admin)->putJson('Shop/Management/Purchasable/Price', [
			'purchasable_id' => $this->purchasable->id,
			'description' => 'With sizes',
			'note' => '',
			'prices' => [
				['size_variant_type' => 'medium', 'license_type' => 'personal', 'price' => 1999],
			],
			'print_sizes' => [
				['print_size_id' => $this->print_size1->id, 'price' => 2500],
			],
			'pixel_sizes' => [
				['pixel_size_id' => $this->pixel_size1->id, 'price' => 1200, 'license_type' => 'personal'],
			],
		]);

		$this->assertOk($response);
		$response->assertJsonStructure([
			'purchasable_id',
			'prices',
			'print_sizes',
			'pixel_sizes',
		]);
		$this->assertCount(1, $response->json('print_sizes'));
		$this->assertCount(1, $response->json('pixel_sizes'));
	}

	public function testUpdateWithoutSizesFieldsStillSucceeds(): void
	{
		// Omitting print_sizes/pixel_sizes entirely (they are optional)
		$response = $this->actingAs($this->admin)->putJson('Shop/Management/Purchasable/Price', [
			'purchasable_id' => $this->purchasable->id,
			'description' => 'No sizes',
			'note' => '',
			'prices' => [
				['size_variant_type' => 'medium', 'license_type' => 'personal', 'price' => 1999],
			],
		]);

		$this->assertOk($response);
	}
}
