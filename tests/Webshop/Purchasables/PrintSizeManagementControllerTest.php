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

use App\Models\PrintSize;
use Tests\Feature_v2\Base\BaseApiWithDataTest;
use Tests\Traits\RequirePro;

/**
 * Test class for PrintSizeManagementController.
 *
 * Tests admin CRUD operations for the global print size catalogue.
 */
class PrintSizeManagementControllerTest extends BaseApiWithDataTest
{
	use RequirePro;

	public function setUp(): void
	{
		parent::setUp();
		$this->requirePro();
	}

	public function tearDown(): void
	{
		PrintSize::query()->delete();
		$this->resetPro();
		parent::tearDown();
	}

	public function testIndexReturnsEmptyList(): void
	{
		$response = $this->actingAs($this->admin)->getJson('Shop/Management/PrintSize');

		$this->assertOk($response);
		$response->assertJson([]);
	}

	public function testStoreCreatesNewPrintSize(): void
	{
		$response = $this->actingAs($this->admin)->postJson('Shop/Management/PrintSize', [
			'label' => '10x15 cm',
			'width' => 10,
			'height' => 15,
			'unit' => 'cm',
			'paper_type' => 'Matte',
			'is_active' => true,
		]);

		$this->assertCreated($response);
		$response->assertJsonStructure(['id', 'label', 'width', 'height', 'unit', 'paper_type', 'is_active']);
		$response->assertJson([
			'label' => '10x15 cm',
			'width' => 10,
			'height' => 15,
			'unit' => 'cm',
			'paper_type' => 'Matte',
			'is_active' => true,
		]);

		$this->assertDatabaseHas('print_sizes', ['label' => '10x15 cm', 'unit' => 'cm']);
	}

	public function testStoreWithoutPaperType(): void
	{
		$response = $this->actingAs($this->admin)->postJson('Shop/Management/PrintSize', [
			'label' => '20x30 cm',
			'width' => 20,
			'height' => 30,
			'unit' => 'cm',
			'paper_type' => null,
			'is_active' => false,
		]);

		$this->assertCreated($response);
		$response->assertJson(['paper_type' => null, 'is_active' => false]);
	}

	public function testUpdateModifiesPrintSize(): void
	{
		$print_size = PrintSize::factory()->create(['label' => 'Old Label', 'is_active' => true]);

		$response = $this->actingAs($this->admin)->putJson('Shop/Management/PrintSize', [
			'print_size_id' => $print_size->id,
			'label' => 'New Label',
			'width' => 25,
			'height' => 35,
			'unit' => 'inch',
			'paper_type' => 'Glossy',
			'is_active' => false,
		]);

		$this->assertOk($response);
		$response->assertJson(['label' => 'New Label', 'is_active' => false]);

		$this->assertDatabaseHas('print_sizes', ['id' => $print_size->id, 'label' => 'New Label', 'unit' => 'inch']);
	}

	public function testDestroyDeletesPrintSize(): void
	{
		$print_size = PrintSize::factory()->create();

		$response = $this->actingAs($this->admin)->deleteJson('Shop/Management/PrintSize', [
			'print_size_id' => $print_size->id,
		]);

		$this->assertNoContent($response);
		$this->assertDatabaseMissing('print_sizes', ['id' => $print_size->id]);
	}

	public function testIndexListsAllSizes(): void
	{
		PrintSize::factory()->count(3)->create();

		$response = $this->actingAs($this->admin)->getJson('Shop/Management/PrintSize');

		$this->assertOk($response);
		$this->assertCount(3, $response->json());
	}

	public function testStoreRequiresAuth(): void
	{
		$response = $this->postJson('Shop/Management/PrintSize', [
			'label' => 'Test',
			'width' => 10,
			'height' => 15,
			'unit' => 'cm',
			'is_active' => true,
		]);

		$this->assertUnauthorized($response);
	}

	public function testStoreRequiresOwner(): void
	{
		$response = $this->actingAs($this->userMayUpload1)->postJson('Shop/Management/PrintSize', [
			'label' => 'Test',
			'width' => 10,
			'height' => 15,
			'unit' => 'cm',
			'is_active' => true,
		]);

		$this->assertForbidden($response);
	}

	public function testStoreValidatesUnit(): void
	{
		$response = $this->actingAs($this->admin)->postJson('Shop/Management/PrintSize', [
			'label' => 'Test',
			'width' => 10,
			'height' => 15,
			'unit' => 'mm', // invalid
			'is_active' => true,
		]);

		$this->assertUnprocessable($response);
		$response->assertJsonValidationErrors(['unit']);
	}
}
