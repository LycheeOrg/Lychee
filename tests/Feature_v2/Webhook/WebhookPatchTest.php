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

namespace Tests\Feature_v2\Webhook;

use App\Models\Webhook;
use Illuminate\Support\Facades\Config;
use Tests\Feature_v2\Base\BaseApiWithDataTest;
use Tests\Traits\RequiresEmptyWebhooks;

/**
 * Feature tests for PATCH /Webhook/{id} (partial update).
 */
class WebhookPatchTest extends BaseApiWithDataTest
{
	use RequiresEmptyWebhooks;

	private Webhook $webhook;

	public function setUp(): void
	{
		parent::setUp();
		$this->setUpRequiresEmptyWebhooks();
		Config::set('features.webhook', true);

		$this->webhook = Webhook::factory()->onPhotoAdd()->jsonFormat()->create([
			'enabled' => true,
			'name' => 'Original Name',
		]);
	}

	public function tearDown(): void
	{
		$this->tearDownRequiresEmptyWebhooks();
		parent::tearDown();
	}

	public function testPatchRequiresAuthentication(): void
	{
		$response = $this->patchJson("Webhook/{$this->webhook->id}", ['enabled' => false]);
		$this->assertUnauthorized($response);
	}

	public function testPatchForbiddenForNonAdmin(): void
	{
		$response = $this->actingAs($this->userMayUpload1)->patchJson("Webhook/{$this->webhook->id}", ['enabled' => false]);
		$this->assertForbidden($response);
	}

	public function testPatchForbiddenWhenFeatureInactive(): void
	{
		Config::set('features.webhook', false);
		$response = $this->actingAs($this->admin)->patchJson("Webhook/{$this->webhook->id}", ['enabled' => false]);
		$this->assertForbidden($response);
	}

	public function testPatchUpdatesOnlySuppliedField(): void
	{
		$originalEvent = $this->webhook->event->value;

		$response = $this->actingAs($this->admin)->patchJson("Webhook/{$this->webhook->id}", [
			'name' => 'New Name',
		]);
		$this->assertOk($response);

		$response->assertJsonPath('name', 'New Name');
		// Event must remain unchanged
		$response->assertJsonPath('event', $originalEvent);
	}

	public function testPatchTogglesEnabled(): void
	{
		// Disable
		$response = $this->actingAs($this->admin)->patchJson("Webhook/{$this->webhook->id}", ['enabled' => false]);
		$this->assertOk($response);
		$response->assertJsonPath('enabled', false);

		// Re-enable
		$response = $this->actingAs($this->admin)->patchJson("Webhook/{$this->webhook->id}", ['enabled' => true]);
		$this->assertOk($response);
		$response->assertJsonPath('enabled', true);
	}

	public function testPatchNotFoundForMissingWebhook(): void
	{
		$response = $this->actingAs($this->admin)->patchJson('Webhook/nonexistent-id', ['enabled' => false]);
		$this->assertNotFound($response);
	}

	public function testPatchRejectsInvalidEventWhenProvided(): void
	{
		$response = $this->actingAs($this->admin)->patchJson("Webhook/{$this->webhook->id}", [
			'event' => 'bad.event',
		]);
		$this->assertUnprocessable($response);
	}

	public function testPatchSetsSecret(): void
	{
		$response = $this->actingAs($this->admin)->patchJson("Webhook/{$this->webhook->id}", [
			'secret' => 'patched-secret',
			'secret_header' => 'X-Patched',
		]);
		$this->assertOk($response);
		$response->assertJsonPath('has_secret', true);
		$response->assertJsonPath('secret_header', 'X-Patched');
	}

	public function testPatchClearsSecret(): void
	{
		// First set a secret
		$this->webhook->secret = 'existing-secret';
		$this->webhook->save();

		// Now clear it
		$response = $this->actingAs($this->admin)->patchJson("Webhook/{$this->webhook->id}", [
			'secret' => null,
		]);
		$this->assertOk($response);
		$response->assertJsonPath('has_secret', false);
	}
}
