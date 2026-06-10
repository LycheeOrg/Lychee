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

use App\Actions\Shop\PurchasableService;
use App\DTO\PixelSizeAssignment;
use App\DTO\PrintSizeAssignment;
use App\Enum\PurchasableLicenseType;
use App\Models\PixelSize;
use App\Models\PrintSize;
use App\Models\Purchasable;
use App\Models\PurchasablePixelSize;
use App\Models\PurchasablePrintSize;
use App\Services\MoneyService;
use Tests\Feature_v2\Base\BaseApiWithDataTest;
use Tests\Traits\RequirePro;

/**
 * Unit tests for PurchasableService::syncPrintSizes and ::syncPixelSizes.
 *
 * Tests T-043-37: Size sync methods correctly replace existing assignments.
 */
class PurchasableSyncSizesTest extends BaseApiWithDataTest
{
	use RequirePro;

	private PurchasableService $service;
	private MoneyService $money_service;
	private Purchasable $purchasable;

	public function setUp(): void
	{
		parent::setUp();
		$this->requirePro();

		$this->service = resolve(PurchasableService::class);
		$this->money_service = resolve(MoneyService::class);

		$this->purchasable = Purchasable::factory()->forPhoto($this->photo1->id, $this->album1->id)->create();
	}

	public function tearDown(): void
	{
		$this->resetPro();
		parent::tearDown();
	}

	public function testSyncPrintSizesCreatesAssignments(): void
	{
		$print_size1 = PrintSize::factory()->create();
		$print_size2 = PrintSize::factory()->create();

		$assignments = [
			new PrintSizeAssignment($print_size1->id, $this->money_service->createFromCents(2500)),
			new PrintSizeAssignment($print_size2->id, $this->money_service->createFromCents(3500)),
		];

		$this->service->syncPrintSizes($this->purchasable, $assignments);

		$this->assertDatabaseHas('purchasable_print_sizes', [
			'purchasable_id' => $this->purchasable->id,
			'print_size_id' => $print_size1->id,
		]);
		$this->assertDatabaseHas('purchasable_print_sizes', [
			'purchasable_id' => $this->purchasable->id,
			'print_size_id' => $print_size2->id,
		]);
	}

	public function testSyncPrintSizesReplacesExistingAssignments(): void
	{
		$print_size1 = PrintSize::factory()->create();
		$print_size2 = PrintSize::factory()->create();

		// Pre-populate with print_size1
		PurchasablePrintSize::factory()->create([
			'purchasable_id' => $this->purchasable->id,
			'print_size_id' => $print_size1->id,
		]);

		// Sync with only print_size2
		$assignments = [
			new PrintSizeAssignment($print_size2->id, $this->money_service->createFromCents(3500)),
		];
		$this->service->syncPrintSizes($this->purchasable, $assignments);

		$this->assertDatabaseMissing('purchasable_print_sizes', [
			'purchasable_id' => $this->purchasable->id,
			'print_size_id' => $print_size1->id,
		]);
		$this->assertDatabaseHas('purchasable_print_sizes', [
			'purchasable_id' => $this->purchasable->id,
			'print_size_id' => $print_size2->id,
		]);
	}

	public function testSyncPrintSizesWithEmptyArrayClearsAllAssignments(): void
	{
		$print_size = PrintSize::factory()->create();

		PurchasablePrintSize::factory()->create([
			'purchasable_id' => $this->purchasable->id,
			'print_size_id' => $print_size->id,
		]);

		$this->service->syncPrintSizes($this->purchasable, []);

		$this->assertDatabaseMissing('purchasable_print_sizes', [
			'purchasable_id' => $this->purchasable->id,
		]);
	}

	public function testSyncPixelSizesCreatesAssignments(): void
	{
		$pixel_size1 = PixelSize::factory()->create();
		$pixel_size2 = PixelSize::factory()->create();

		$assignments = [
			new PixelSizeAssignment($pixel_size1->id, $this->money_service->createFromCents(1200), PurchasableLicenseType::PERSONAL),
			new PixelSizeAssignment($pixel_size2->id, $this->money_service->createFromCents(1800), PurchasableLicenseType::PERSONAL),
		];

		$this->service->syncPixelSizes($this->purchasable, $assignments);

		$this->assertDatabaseHas('purchasable_pixel_sizes', [
			'purchasable_id' => $this->purchasable->id,
			'pixel_size_id' => $pixel_size1->id,
		]);
		$this->assertDatabaseHas('purchasable_pixel_sizes', [
			'purchasable_id' => $this->purchasable->id,
			'pixel_size_id' => $pixel_size2->id,
		]);
	}

	public function testSyncPixelSizesReplacesExistingAssignments(): void
	{
		$pixel_size1 = PixelSize::factory()->create();
		$pixel_size2 = PixelSize::factory()->create();

		PurchasablePixelSize::factory()->create([
			'purchasable_id' => $this->purchasable->id,
			'pixel_size_id' => $pixel_size1->id,
		]);

		$assignments = [
			new PixelSizeAssignment($pixel_size2->id, $this->money_service->createFromCents(1800), PurchasableLicenseType::PERSONAL),
		];
		$this->service->syncPixelSizes($this->purchasable, $assignments);

		$this->assertDatabaseMissing('purchasable_pixel_sizes', [
			'purchasable_id' => $this->purchasable->id,
			'pixel_size_id' => $pixel_size1->id,
		]);
		$this->assertDatabaseHas('purchasable_pixel_sizes', [
			'purchasable_id' => $this->purchasable->id,
			'pixel_size_id' => $pixel_size2->id,
		]);
	}

	public function testSyncPixelSizesWithEmptyArrayClearsAllAssignments(): void
	{
		$pixel_size = PixelSize::factory()->create();

		PurchasablePixelSize::factory()->create([
			'purchasable_id' => $this->purchasable->id,
			'pixel_size_id' => $pixel_size->id,
		]);

		$this->service->syncPixelSizes($this->purchasable, []);

		$this->assertDatabaseMissing('purchasable_pixel_sizes', [
			'purchasable_id' => $this->purchasable->id,
		]);
	}

	public function testSyncPrintSizesReturnsPurchasable(): void
	{
		$result = $this->service->syncPrintSizes($this->purchasable, []);
		$this->assertInstanceOf(Purchasable::class, $result);
		$this->assertEquals($this->purchasable->id, $result->id);
	}

	public function testSyncPixelSizesReturnsPurchasable(): void
	{
		$result = $this->service->syncPixelSizes($this->purchasable, []);
		$this->assertInstanceOf(Purchasable::class, $result);
		$this->assertEquals($this->purchasable->id, $result->id);
	}
}
