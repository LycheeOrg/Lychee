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
 * Feature tests for PUT /Webhook/{id} (full update).
 */
class WebhookUpdateTest extends BaseApiWithDataTest
{
	use RequiresEmptyWebhooks;

	private Webhook $webhook;

	/** @var array<string,mixed> */
	private array $validPayload;

	public function setUp(): void
	{
		parent::setUp();
		$this->setUpRequiresEmptyWebhooks();
		Config::set('features.webhook', true);

		$this->webhook = Webhook::factory()->onPhotoAdd()->jsonFormat()->create();

		$this->validPayload = [
			'name' => 'Updated Name',
			'event' => 'photo.move',
			'method' => 'PUT',
			'url' => 'https://hooks.example.com/updated',
			'payload_format' => 'query_string',
			'enabled' => false,
			'send_photo_id' => false,
			'send_album_id' => true,
			'send_title' => false,
			'send_size_variants' => false,
		];
	}

	public function tearDown(): void
	{
		$this->tearDownRequiresEmptyWebhooks();
		parent::tearDown();
	}

	public function testUpdateRequiresAuthentication(): void
	{
		$response = $this->putJson("Webhook/{$this->webhook->id}", $this->validPayload);
		$this->assertUnauthorized($response);
	}

	public function testUpdateForbiddenForNonAdmin(): void
	{
		$response = $this->actingAs($this->userMayUpload1)->putJson("Webhook/{$this->webhook->id}", $this->validPayload);
		$this->assertForbidden($response);
	}

	public function testUpdateForbiddenWhenFeatureInactive(): void
	{
		Config::set('features.webhook', false);
		$response = $this->actingAs($this->admin)->putJson("Webhook/{$this->webhook->id}", $this->validPayload);
		$this->assertForbidden($response);
	}

	public function testUpdateReplacesAllFields(): void
	{
		$response = $this->actingAs($this->admin)->putJson("Webhook/{$this->webhook->id}", $this->validPayload);
		$this->assertOk($response);

		$response->assertJsonPath('name', 'Updated Name');
		$response->assertJsonPath('event', 'photo.move');
		$response->assertJsonPath('method', 'PUT');
		$response->assertJsonPath('url', 'https://hooks.example.com/updated');
		$response->assertJsonPath('payload_format', 'query_string');
		$response->assertJsonPath('enabled', false);
		$response->assertJsonPath('send_photo_id', false);
		$response->assertJsonPath('send_album_id', true);
	}

	public function testUpdateNotFoundForMissingWebhook(): void
	{
		$response = $this->actingAs($this->admin)->putJson('Webhook/nonexistent-id', $this->validPayload);
		$this->assertNotFound($response);
	}

	public function testUpdateRejectsInvalidFields(): void
	{
		$payload = array_merge($this->validPayload, ['event' => 'bad.event']);
		$response = $this->actingAs($this->admin)->putJson("Webhook/{$this->webhook->id}", $payload);
		$this->assertUnprocessable($response);
	}

	public function testUpdateMissingRequiredFieldsFails(): void
	{
		$response = $this->actingAs($this->admin)->putJson("Webhook/{$this->webhook->id}", []);
		$this->assertUnprocessable($response);
	}

	public function testUpdateUpdatesSecretWhenProvided(): void
	{
		$payload = array_merge($this->validPayload, [
			'secret' => 'new-secret',
			'secret_header' => 'X-New-Sig',
		]);
		$response = $this->actingAs($this->admin)->putJson("Webhook/{$this->webhook->id}", $payload);
		$this->assertOk($response);
		$response->assertJsonPath('has_secret', true);
		$response->assertJsonPath('secret_header', 'X-New-Sig');
	}
}
