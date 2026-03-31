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
 * Feature tests for GET /Webhook (list all webhooks).
 */
class WebhookIndexTest extends BaseApiWithDataTest
{
	use RequiresEmptyWebhooks;

	public function setUp(): void
	{
		parent::setUp();
		$this->setUpRequiresEmptyWebhooks();
		Config::set('features.webhook', true);
	}

	public function tearDown(): void
	{
		$this->tearDownRequiresEmptyWebhooks();
		parent::tearDown();
	}

	public function testIndexRequiresAuthentication(): void
	{
		$response = $this->getJson('Webhook');
		$this->assertUnauthorized($response);
	}

	public function testIndexForbiddenForNonAdmin(): void
	{
		$response = $this->actingAs($this->userMayUpload1)->getJson('Webhook');
		$this->assertForbidden($response);
	}

	public function testIndexForbiddenWhenFeatureInactive(): void
	{
		Config::set('features.webhook', false);
		$response = $this->actingAs($this->admin)->getJson('Webhook');
		$this->assertForbidden($response);
	}

	public function testIndexReturnsEmptyListForAdmin(): void
	{
		$response = $this->actingAs($this->admin)->getJson('Webhook');
		$this->assertOk($response);
		$response->assertJsonStructure(['webhooks', 'current_page', 'last_page', 'per_page', 'total']);
		$response->assertJsonPath('total', 0);
	}

	public function testIndexReturnsPaginatedWebhooks(): void
	{
		Webhook::factory()->count(3)->create();

		$response = $this->actingAs($this->admin)->getJson('Webhook');
		$this->assertOk($response);
		$response->assertJsonPath('total', 3);
		$this->assertCount(3, $response->json('webhooks'));
	}

	public function testIndexWebhookResourceStructure(): void
	{
		Webhook::factory()->onPhotoAdd()->jsonFormat()->create();

		$response = $this->actingAs($this->admin)->getJson('Webhook');
		$this->assertOk($response);
		$response->assertJsonStructure([
			'webhooks' => [
				'*' => [
					'id',
					'name',
					'event',
					'method',
					'url',
					'payload_format',
					'has_secret',
					'secret_header',
					'enabled',
					'send_photo_id',
					'send_album_id',
					'send_title',
					'send_size_variants',
					'size_variant_types',
					'created_at',
					'updated_at',
				],
			],
		]);
	}
}
