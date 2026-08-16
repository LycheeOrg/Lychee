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

class LandingLinkCrudTest extends BaseApiWithDataTest
{
	use RequiresEmptyLandingLinks;

	private array $validPayload;

	public function setUp(): void
	{
		parent::setUp();
		$this->setUpRequiresEmptyLandingLinks();

		$this->validPayload = [
			'label' => 'My Link',
			'url' => 'https://example.com/press',
			'placement' => 'nav',
			'open_in_new_tab' => true,
			'sort_order' => 0,
			'enabled' => true,
		];
	}

	public function tearDown(): void
	{
		$this->tearDownRequiresEmptyLandingLinks();
		parent::tearDown();
	}

	public function testStoreRequiresAuthentication(): void
	{
		$response = $this->postJson('LandingLink', $this->validPayload);
		$this->assertUnauthorized($response);
	}

	public function testStoreForbiddenForNonAdmin(): void
	{
		$response = $this->actingAs($this->userMayUpload1)->postJson('LandingLink', $this->validPayload);
		$this->assertForbidden($response);
	}

	public function testStoreCreatesLandingLink(): void
	{
		$response = $this->actingAs($this->admin)->postJson('LandingLink', $this->validPayload);
		$this->assertCreated($response);

		$response->assertJsonPath('label', 'My Link');
		$response->assertJsonPath('url', 'https://example.com/press');
		$response->assertJsonPath('placement', 'nav');
		$response->assertJsonPath('open_in_new_tab', true);
		$response->assertJsonPath('enabled', true);
		$this->assertDatabaseCount('landing_links', 1);
	}

	public function testStoreRejectsInvalidUrl(): void
	{
		$payload = $this->validPayload;
		$payload['url'] = 'not-a-url';
		$response = $this->actingAs($this->admin)->postJson('LandingLink', $payload);
		$this->assertUnprocessable($response);
	}

	public function testStoreRejectsInvalidPlacement(): void
	{
		$payload = $this->validPayload;
		$payload['placement'] = 'sidebar';
		$response = $this->actingAs($this->admin)->postJson('LandingLink', $payload);
		$this->assertUnprocessable($response);
	}

	public function testStoreRejectsMissingLabel(): void
	{
		$payload = $this->validPayload;
		unset($payload['label']);
		$response = $this->actingAs($this->admin)->postJson('LandingLink', $payload);
		$this->assertUnprocessable($response);
	}

	public function testIndexRequiresAdmin(): void
	{
		$response = $this->actingAs($this->userMayUpload1)->getJson('LandingLink');
		$this->assertForbidden($response);
	}

	public function testIndexReturnsAllLinks(): void
	{
		LandingLink::factory()->count(3)->create();

		$response = $this->actingAs($this->admin)->getJson('LandingLink');
		$this->assertOk($response);
		$response->assertJsonCount(3, 'landing_links');
	}

	public function testShowReturnsLandingLink(): void
	{
		$link = LandingLink::factory()->create();

		$response = $this->actingAs($this->admin)->getJson("LandingLink/{$link->id}");
		$this->assertOk($response);
		$response->assertJsonPath('id', $link->id);
	}

	public function testShowReturnsNotFoundForMissingLandingLink(): void
	{
		$response = $this->actingAs($this->admin)->getJson('LandingLink/nonexistent-id');
		$this->assertNotFound($response);
	}

	public function testUpdateReplacesLandingLink(): void
	{
		$link = LandingLink::factory()->create();

		$payload = $this->validPayload;
		$payload['label'] = 'Updated Label';
		$response = $this->actingAs($this->admin)->putJson("LandingLink/{$link->id}", $payload);
		$this->assertOk($response);
		$response->assertJsonPath('label', 'Updated Label');
	}

	public function testPatchUpdatesPartialFields(): void
	{
		$link = LandingLink::factory()->create(['enabled' => true]);

		$response = $this->actingAs($this->admin)->patchJson("LandingLink/{$link->id}", ['enabled' => false]);
		$this->assertOk($response);
		$response->assertJsonPath('enabled', false);
	}

	public function testDestroyRemovesLandingLink(): void
	{
		$link = LandingLink::factory()->create();

		$response = $this->actingAs($this->admin)->deleteJson("LandingLink/{$link->id}");
		$this->assertNoContent($response);
		$this->assertDatabaseCount('landing_links', 0);
	}

	public function testDestroyForbiddenForNonAdmin(): void
	{
		$link = LandingLink::factory()->create();

		$response = $this->actingAs($this->userMayUpload1)->deleteJson("LandingLink/{$link->id}");
		$this->assertForbidden($response);
		$this->assertDatabaseCount('landing_links', 1);
	}
}
