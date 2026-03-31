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
 * Feature tests for GET /Webhook/{id} (show single webhook).
 */
class WebhookShowTest extends BaseApiWithDataTest
{
	use RequiresEmptyWebhooks;

	private Webhook $webhook;

	public function setUp(): void
	{
		parent::setUp();
		$this->setUpRequiresEmptyWebhooks();
		Config::set('features.webhook', true);

		$this->webhook = Webhook::factory()->onPhotoAdd()->jsonFormat()->create();
	}

	public function tearDown(): void
	{
		$this->tearDownRequiresEmptyWebhooks();
		parent::tearDown();
	}

	public function testShowRequiresAuthentication(): void
	{
		$response = $this->getJson("Webhook/{$this->webhook->id}");
		$this->assertUnauthorized($response);
	}

	public function testShowForbiddenForNonAdmin(): void
	{
		$response = $this->actingAs($this->userMayUpload1)->getJson("Webhook/{$this->webhook->id}");
		$this->assertForbidden($response);
	}

	public function testShowForbiddenWhenFeatureInactive(): void
	{
		Config::set('features.webhook', false);
		$response = $this->actingAs($this->admin)->getJson("Webhook/{$this->webhook->id}");
		$this->assertForbidden($response);
	}

	public function testShowReturnsWebhook(): void
	{
		$response = $this->actingAs($this->admin)->getJson("Webhook/{$this->webhook->id}");
		$this->assertOk($response);

		$response->assertJsonPath('id', $this->webhook->id);
		$response->assertJsonPath('event', 'photo.add');
		$response->assertJsonPath('payload_format', 'json');
		$response->assertJsonPath('has_secret', false);
	}

	public function testShowReturnsNotFoundForMissingWebhook(): void
	{
		$response = $this->actingAs($this->admin)->getJson('Webhook/nonexistent-webhook-id');
		$this->assertNotFound($response);
	}

	public function testShowDoesNotExposeSecret(): void
	{
		$webhook = Webhook::factory()->withSecret('hidden-secret')->create();

		$response = $this->actingAs($this->admin)->getJson("Webhook/{$webhook->id}");
		$this->assertOk($response);
		$response->assertJsonPath('has_secret', true);
		$response->assertJsonMissing(['secret']);
	}
}
