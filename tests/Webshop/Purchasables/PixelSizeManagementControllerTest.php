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
use Tests\Feature_v2\Base\BaseApiWithDataTest;
use Tests\Traits\RequirePro;

/**
 * Test class for PixelSizeManagementController.
 *
 * Tests admin CRUD operations for the global pixel size catalogue.
 */
class PixelSizeManagementControllerTest extends BaseApiWithDataTest
{
	use RequirePro;

	public function setUp(): void
	{
		parent::setUp();
		$this->requirePro();
	}

	public function tearDown(): void
	{
		PixelSize::query()->delete();
		$this->resetPro();
		parent::tearDown();
	}

	public function testIndexReturnsEmptyList(): void
	{
		$response = $this->actingAs($this->admin)->getJson('Shop/Management/PixelSize');

		$this->assertOk($response);
		$response->assertJson([]);
	}

	public function testStoreCreatesNewPixelSize(): void
	{
		$response = $this->actingAs($this->admin)->postJson('Shop/Management/PixelSize', [
			'label' => 'HD 1920×1080',
			'width' => 1920,
			'height' => 1080,
			'is_active' => true,
		]);

		$this->assertCreated($response);
		$response->assertJsonStructure(['id', 'label', 'width', 'height', 'is_active']);
		$response->assertJson([
			'label' => 'HD 1920×1080',
			'width' => 1920,
			'height' => 1080,
			'is_active' => true,
		]);

		$this->assertDatabaseHas('pixel_sizes', ['label' => 'HD 1920×1080', 'width' => 1920, 'height' => 1080]);
	}

	public function testUpdateModifiesPixelSize(): void
	{
		$pixel_size = PixelSize::factory()->create(['label' => 'Old Label', 'width' => 800, 'height' => 600, 'is_active' => true]);

		$response = $this->actingAs($this->admin)->putJson('Shop/Management/PixelSize', [
			'pixel_size_id' => $pixel_size->id,
			'label' => 'New Label',
			'width' => 2048,
			'height' => 1536,
			'is_active' => false,
		]);

		$this->assertOk($response);
		$response->assertJson(['label' => 'New Label', 'width' => 2048, 'height' => 1536, 'is_active' => false]);

		$this->assertDatabaseHas('pixel_sizes', ['id' => $pixel_size->id, 'label' => 'New Label', 'width' => 2048]);
	}

	public function testDestroyDeletesPixelSize(): void
	{
		$pixel_size = PixelSize::factory()->create();

		$response = $this->actingAs($this->admin)->deleteJson('Shop/Management/PixelSize', [
			'pixel_size_id' => $pixel_size->id,
		]);

		$this->assertNoContent($response);
		$this->assertDatabaseMissing('pixel_sizes', ['id' => $pixel_size->id]);
	}

	public function testIndexListsAllSizes(): void
	{
		PixelSize::factory()->count(3)->create();

		$response = $this->actingAs($this->admin)->getJson('Shop/Management/PixelSize');

		$this->assertOk($response);
		$this->assertCount(3, $response->json());
	}

	public function testStoreRequiresAuth(): void
	{
		$response = $this->postJson('Shop/Management/PixelSize', [
			'label' => 'Test',
			'width' => 2048,
			'height' => 1536,
			'is_active' => true,
		]);

		$this->assertUnauthorized($response);
	}

	public function testStoreRequiresOwner(): void
	{
		$response = $this->actingAs($this->userMayUpload1)->postJson('Shop/Management/PixelSize', [
			'label' => 'Test',
			'width' => 2048,
			'height' => 1536,
			'is_active' => true,
		]);

		$this->assertForbidden($response);
	}

	public function testStoreValidatesLongEdgePixels(): void
	{
		$response = $this->actingAs($this->admin)->postJson('Shop/Management/PixelSize', [
			'label' => 'Test',
			'width' => 0, // invalid: must be at least 1
			'height' => 600,
			'is_active' => true,
		]);

		$this->assertUnprocessable($response);
		$response->assertJsonValidationErrors(['width']);
	}

	public function testStoreValidationRequiresLabel(): void
	{
		$response = $this->actingAs($this->admin)->postJson('Shop/Management/PixelSize', [
			'width' => 1920,
			'height' => 1080,
			'is_active' => true,
		]);

		$this->assertUnprocessable($response);
		$response->assertJsonValidationErrors(['label']);
	}
}
