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

namespace Tests\Unit\Listeners;

use App\Events\PhotoAdded;
use App\Events\PhotoMoved;
use App\Events\PhotoWillBeDeleted;
use App\Jobs\WebhookDispatchJob;
use App\Listeners\WebhookListener;
use App\Models\Webhook;
use App\Services\Webhook\WebhookPayloadBuilder;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Config;
use Tests\AbstractTestCase;
use Tests\Traits\RequiresEmptyWebhooks;

/**
 * Unit tests for WebhookListener.
 *
 * Tests the three handler methods:
 * - handlePhotoAdded
 * - handlePhotoMoved
 * - handlePhotoWillBeDeleted
 *
 * Each handler is guarded by the `features.webhook` config flag.
 */
class WebhookListenerTest extends AbstractTestCase
{
	use RequiresEmptyWebhooks;

	private WebhookListener $listener;

	public function setUp(): void
	{
		parent::setUp();
		$this->setUpRequiresEmptyWebhooks();

		// Enable the webhook feature for all tests in this class.
		Config::set('features.webhook', true);

		$builder = new WebhookPayloadBuilder();
		$this->listener = new WebhookListener($builder);
	}

	public function tearDown(): void
	{
		$this->tearDownRequiresEmptyWebhooks();
		parent::tearDown();
	}

	// ── feature gate ─────────────────────────────────────────────────────────

	public function testHandlePhotoAddedDoesNothingWhenFeatureInactive(): void
	{
		Config::set('features.webhook', false);
		Bus::fake();

		$this->listener->handlePhotoAdded(new PhotoAdded('photo-001'));

		Bus::assertNothingDispatched();
	}

	public function testHandlePhotoMovedDoesNothingWhenFeatureInactive(): void
	{
		Config::set('features.webhook', false);
		Bus::fake();

		$this->listener->handlePhotoMoved(new PhotoMoved('photo-001', 'album-a', 'album-b'));

		Bus::assertNothingDispatched();
	}

	public function testHandlePhotoWillBeDeletedDoesNothingWhenFeatureInactive(): void
	{
		Config::set('features.webhook', false);
		Bus::fake();

		// Create an enabled delete-webhook so we are sure the gate fires before any DB query.
		Webhook::factory()->onPhotoDelete()->create();

		$this->listener->handlePhotoWillBeDeleted(
			new PhotoWillBeDeleted('photo-001', 'album-001', 'My Photo', []),
		);

		Bus::assertNothingDispatched();
	}

	// ── handlePhotoWillBeDeleted with data ───────────────────────────────────

	public function testHandlePhotoWillBeDeletedDispatchesJobForEachEnabledWebhook(): void
	{
		Bus::fake();

		// Two enabled delete-webhooks and one disabled
		Webhook::factory()->onPhotoDelete()->count(2)->create();
		Webhook::factory()->onPhotoDelete()->disabled()->create();

		$event = new PhotoWillBeDeleted(
			photo_id: 'photo-001',
			album_id: 'album-001',
			title: 'Test Photo',
			size_variants: [['type' => 'original', 'url' => 'https://example.com/o.jpg']],
		);

		$this->listener->handlePhotoWillBeDeleted($event);

		Bus::assertDispatchedTimes(WebhookDispatchJob::class, 2);
	}

	public function testHandlePhotoWillBeDeletedDoesNotDispatchWhenNoMatchingWebhooks(): void
	{
		Bus::fake();

		// Only an ADD-webhook exists, not DELETE
		Webhook::factory()->onPhotoAdd()->create();

		$this->listener->handlePhotoWillBeDeleted(
			new PhotoWillBeDeleted('photo-001', 'album-001', 'Title', []),
		);

		Bus::assertNothingDispatched();
	}

	// ── handlePhotoAdded / handlePhotoMoved (no DB photo) ────────────────────

	public function testHandlePhotoAddedDoesNotDispatchWhenNoMatchingWebhooks(): void
	{
		Bus::fake();

		// No webhooks at all
		$this->listener->handlePhotoAdded(new PhotoAdded('nonexistent-photo'));

		Bus::assertNothingDispatched();
	}

	public function testHandlePhotoMovedDoesNotDispatchWhenNoMatchingWebhooks(): void
	{
		Bus::fake();

		// Only a DELETE-webhook exists, not MOVE
		Webhook::factory()->onPhotoDelete()->create();

		$this->listener->handlePhotoMoved(new PhotoMoved('nonexistent-photo', 'album-a', 'album-b'));

		Bus::assertNothingDispatched();
	}

	public function testHandlePhotoAddedLogsWarningWhenPhotoNotFound(): void
	{
		Bus::fake();

		// An enabled ADD-webhook exists, but the photo does not.
		Webhook::factory()->onPhotoAdd()->create();

		\Illuminate\Support\Facades\Log::shouldReceive('warning')
			->once()
			->with('WebhookListener: photo not found', \Mockery::type('array'));

		$this->listener->handlePhotoAdded(new PhotoAdded('nonexistent-photo-id'));

		Bus::assertNothingDispatched();
	}
}
