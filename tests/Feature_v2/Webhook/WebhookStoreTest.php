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

use Illuminate\Support\Facades\Config;
use Tests\Feature_v2\Base\BaseApiWithDataTest;
use Tests\Traits\RequiresEmptyWebhooks;

/**
 * Feature tests for POST /Webhook (create webhook).
 */
class WebhookStoreTest extends BaseApiWithDataTest
{
	use RequiresEmptyWebhooks;

	/** @var array<string,mixed> */
	private array $validPayload;

	public function setUp(): void
	{
		parent::setUp();
		$this->setUpRequiresEmptyWebhooks();
		Config::set('features.webhook', true);

		$this->validPayload = [
			'name' => 'My Webhook',
			'event' => 'photo.add',
			'method' => 'POST',
			'url' => 'https://hooks.example.com/notify',
			'payload_format' => 'json',
			'enabled' => true,
			'send_photo_id' => true,
			'send_album_id' => true,
			'send_title' => true,
			'send_size_variants' => false,
		];
	}

	public function tearDown(): void
	{
		$this->tearDownRequiresEmptyWebhooks();
		parent::tearDown();
	}

	public function testStoreRequiresAuthentication(): void
	{
		$response = $this->postJson('Webhook', $this->validPayload);
		$this->assertUnauthorized($response);
	}

	public function testStoreForbiddenForNonAdmin(): void
	{
		$response = $this->actingAs($this->userMayUpload1)->postJson('Webhook', $this->validPayload);
		$this->assertForbidden($response);
	}

	public function testStoreForbiddenWhenFeatureInactive(): void
	{
		Config::set('features.webhook', false);
		$response = $this->actingAs($this->admin)->postJson('Webhook', $this->validPayload);
		$this->assertForbidden($response);
	}

	public function testStoreCreatesWebhook(): void
	{
		$response = $this->actingAs($this->admin)->postJson('Webhook', $this->validPayload);
		$this->assertCreated($response);

		$response->assertJsonPath('name', 'My Webhook');
		$response->assertJsonPath('event', 'photo.add');
		$response->assertJsonPath('method', 'POST');
		$response->assertJsonPath('url', 'https://hooks.example.com/notify');
		$response->assertJsonPath('payload_format', 'json');
		$response->assertJsonPath('enabled', true);
		$response->assertJsonPath('has_secret', false);
		$response->assertJsonMissing(['secret']);

		$this->assertDatabaseCount('webhooks', 1);
	}

	public function testStoreCreatesWebhookWithSecret(): void
	{
		$payload = array_merge($this->validPayload, [
			'secret' => 'my-secret-key',
			'secret_header' => 'X-Signature',
		]);

		$response = $this->actingAs($this->admin)->postJson('Webhook', $payload);
		$this->assertCreated($response);

		// Secret is never exposed, has_secret = true
		$response->assertJsonPath('has_secret', true);
		$response->assertJsonPath('secret_header', 'X-Signature');
		$response->assertJsonMissing(['secret']);
	}

	public function testStoreRejectsInvalidEvent(): void
	{
		$payload = array_merge($this->validPayload, ['event' => 'invalid.event']);
		$response = $this->actingAs($this->admin)->postJson('Webhook', $payload);
		$this->assertUnprocessable($response);
	}

	public function testStoreRejectsInvalidMethod(): void
	{
		$payload = array_merge($this->validPayload, ['method' => 'CONNECT']);
		$response = $this->actingAs($this->admin)->postJson('Webhook', $payload);
		$this->assertUnprocessable($response);
	}

	public function testStoreRejectsInvalidUrl(): void
	{
		$payload = array_merge($this->validPayload, ['url' => 'not-a-url']);
		$response = $this->actingAs($this->admin)->postJson('Webhook', $payload);
		$this->assertUnprocessable($response);
	}

	public function testStoreRejectsInvalidPayloadFormat(): void
	{
		$payload = array_merge($this->validPayload, ['payload_format' => 'xml']);
		$response = $this->actingAs($this->admin)->postJson('Webhook', $payload);
		$this->assertUnprocessable($response);
	}

	public function testStoreMissingRequiredFieldsFails(): void
	{
		$response = $this->actingAs($this->admin)->postJson('Webhook', []);
		$this->assertUnprocessable($response);
	}

	public function testStoreWithQueryStringFormat(): void
	{
		$payload = array_merge($this->validPayload, ['payload_format' => 'query_string']);
		$response = $this->actingAs($this->admin)->postJson('Webhook', $payload);
		$this->assertCreated($response);
		$response->assertJsonPath('payload_format', 'query_string');
	}

	public function testStoreWithAllEvents(): void
	{
		foreach (['photo.add', 'photo.move', 'photo.delete'] as $event) {
			$payload = array_merge($this->validPayload, ['event' => $event]);
			$response = $this->actingAs($this->admin)->postJson('Webhook', $payload);
			$this->assertCreated($response);
			$response->assertJsonPath('event', $event);
		}

		$this->assertDatabaseCount('webhooks', 3);
	}
}
