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

namespace Tests\Unit\Models;

use App\Enum\PhotoWebhookEvent;
use App\Models\Webhook;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\AbstractTestCase;

class WebhookTest extends AbstractTestCase
{
	use DatabaseTransactions;

	public function testBootGeneratesUlidWhenIdNotSet(): void
	{
		$webhook = Webhook::factory()->create();

		self::assertNotNull($webhook->id);
		self::assertEquals(26, strlen($webhook->id));
	}

	public function testScopeEnabledOnlyReturnsEnabledWebhooks(): void
	{
		$enabled = Webhook::factory()->create(['enabled' => true]);
		Webhook::factory()->create(['enabled' => false]);

		$results = Webhook::query()->enabled()->get();

		self::assertCount(1, $results);
		self::assertEquals($enabled->id, $results->first()->id);
	}

	public function testScopeForEventOnlyReturnsMatchingEvent(): void
	{
		$add_hook = Webhook::factory()->create(['event' => PhotoWebhookEvent::ADD]);
		Webhook::factory()->create(['event' => PhotoWebhookEvent::DELETE]);

		$results = Webhook::query()->forEvent(PhotoWebhookEvent::ADD)->get();

		self::assertCount(1, $results);
		self::assertEquals($add_hook->id, $results->first()->id);
	}
}
