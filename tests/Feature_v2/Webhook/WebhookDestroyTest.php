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
 * Feature tests for DELETE /Webhook/{id} (destroy webhook).
 */
class WebhookDestroyTest extends BaseApiWithDataTest
{
	use RequiresEmptyWebhooks;

	private Webhook $webhook;

	public function setUp(): void
	{
		parent::setUp();
		$this->setUpRequiresEmptyWebhooks();
		Config::set('features.webhook', true);

		$this->webhook = Webhook::factory()->onPhotoAdd()->create();
	}

	public function tearDown(): void
	{
		$this->tearDownRequiresEmptyWebhooks();
		parent::tearDown();
	}

	public function testDestroyRequiresAuthentication(): void
	{
		$response = $this->deleteJson("Webhook/{$this->webhook->id}");
		$this->assertUnauthorized($response);
	}

	public function testDestroyForbiddenForNonAdmin(): void
	{
		$response = $this->actingAs($this->userMayUpload1)->deleteJson("Webhook/{$this->webhook->id}");
		$this->assertForbidden($response);
	}

	public function testDestroyForbiddenWhenFeatureInactive(): void
	{
		Config::set('features.webhook', false);
		$response = $this->actingAs($this->admin)->deleteJson("Webhook/{$this->webhook->id}");
		$this->assertForbidden($response);
	}

	public function testDestroyDeletesWebhook(): void
	{
		$this->assertDatabaseHas('webhooks', ['id' => $this->webhook->id]);

		$response = $this->actingAs($this->admin)->deleteJson("Webhook/{$this->webhook->id}");
		$this->assertNoContent($response);

		$this->assertDatabaseMissing('webhooks', ['id' => $this->webhook->id]);
	}

	public function testDestroyReturnsNotFoundForMissingWebhook(): void
	{
		$response = $this->actingAs($this->admin)->deleteJson('Webhook/nonexistent-webhook-id');
		$this->assertNotFound($response);
	}

	public function testDestroyDoesNotAffectOtherWebhooks(): void
	{
		$other = Webhook::factory()->onPhotoDelete()->create();

		$this->actingAs($this->admin)->deleteJson("Webhook/{$this->webhook->id}");

		$this->assertDatabaseHas('webhooks', ['id' => $other->id]);
	}
}
