<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

/**
 * We don't care for unhandled exceptions in tests.
 * It is the nature of a test to throw an exception.
 *
 * @noinspection PhpDocMissingThrowsInspection
 * @noinspection PhpUnhandledExceptionInspection
 */

namespace Tests\Feature_v2\LandingLink;

use App\Models\LandingLink;
use Tests\Feature_v2\Base\BaseApiWithDataTest;
use Tests\Traits\RequiresEmptyLandingLinks;

class LandingLinkBuiltInProtectionTest extends BaseApiWithDataTest
{
	use RequiresEmptyLandingLinks;

	public function setUp(): void
	{
		parent::setUp();
		$this->setUpRequiresEmptyLandingLinks();
	}

	public function tearDown(): void
	{
		$this->tearDownRequiresEmptyLandingLinks();
		parent::tearDown();
	}

	public function testDestroyRejectsBuiltInLink(): void
	{
		$built_in = LandingLink::factory()->builtIn()->create();

		$response = $this->actingAs($this->admin)->deleteJson("LandingLink/{$built_in->id}");
		$this->assertUnprocessable($response);
		$this->assertNotNull($built_in->fresh());
	}

	public function testBuiltInLinkCanBeDisabled(): void
	{
		$built_in = LandingLink::factory()->builtIn()->create(['enabled' => true]);

		$response = $this->actingAs($this->admin)->patchJson("LandingLink/{$built_in->id}", ['enabled' => false]);
		$this->assertOk($response);
		$response->assertJsonPath('enabled', false);
		$response->assertJsonPath('is_built_in', true);
	}

	public function testBuiltInLinkCanBeReordered(): void
	{
		$built_in = LandingLink::factory()->builtIn()->create(['sort_order' => 0]);
		$link1 = LandingLink::factory()->create(['sort_order' => 1]);
		$link2 = LandingLink::factory()->create(['sort_order' => 2]);

		$response = $this->actingAs($this->admin)->patchJson('LandingLink/Reorder', [
			'ids' => [$link2->id, $built_in->id, $link1->id],
		]);
		$this->assertOk($response);

		$this->assertSame(0, $link2->fresh()->sort_order);
		$this->assertSame(1, $built_in->fresh()->sort_order);
		$this->assertSame(2, $link1->fresh()->sort_order);
		$this->assertTrue($built_in->fresh()->is_built_in);
	}

	public function testPatchRejectsUrlChangeOnBuiltInLink(): void
	{
		$built_in = LandingLink::factory()->builtIn()->create(['url' => 'home']);

		$response = $this->actingAs($this->admin)->patchJson("LandingLink/{$built_in->id}", ['url' => 'https://example.com']);
		$this->assertUnprocessable($response);
		$this->assertSame('home', $built_in->fresh()->url);
	}

	public function testPatchAllowsChangingOtherFieldsOnBuiltInLinkWithoutTouchingUrl(): void
	{
		$built_in = LandingLink::factory()->builtIn()->create(['url' => 'home']);

		$response = $this->actingAs($this->admin)->patchJson("LandingLink/{$built_in->id}", ['label' => 'Renamed']);
		$this->assertOk($response);
		$response->assertJsonPath('label', 'Renamed');
		$this->assertSame('home', $built_in->fresh()->url);
	}

	public function testUpdateRejectsUrlChangeOnBuiltInLink(): void
	{
		$built_in = LandingLink::factory()->builtIn()->create(['url' => 'home']);

		$payload = [
			'label' => $built_in->label,
			'url' => 'https://example.com',
			'placement' => $built_in->placement->value,
			'open_in_new_tab' => $built_in->open_in_new_tab,
			'enabled' => $built_in->enabled,
		];

		$response = $this->actingAs($this->admin)->putJson("LandingLink/{$built_in->id}", $payload);
		$this->assertUnprocessable($response);
		$this->assertSame('home', $built_in->fresh()->url);
	}

	public function testStoreCannotCreateABuiltInLink(): void
	{
		$payload = [
			'label' => 'Fake Gallery',
			'url' => 'https://example.com',
			'placement' => 'nav',
			'open_in_new_tab' => true,
			'sort_order' => 0,
			'enabled' => true,
			'is_built_in' => true,
		];

		$response = $this->actingAs($this->admin)->postJson('LandingLink', $payload);
		$this->assertCreated($response);
		$response->assertJsonPath('is_built_in', false);
	}
}
