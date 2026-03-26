<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Console\Commands;

use App\Assets\Features;
use App\DTO\WebhookPayload;
use App\Jobs\WebhookDispatchJob;
use App\Models\Webhook;
use Illuminate\Console\Command;

/**
 * Artisan command to test a webhook configuration by sending a synthetic HTTP request.
 *
 * Usage: php artisan lychee:webhook-test <id>
 *
 * Fires the job synchronously so the operator can immediately see the result.
 */
class WebhookTest extends Command
{
	protected $signature = 'lychee:webhook-test {id : The ULID of the webhook to test}';

	protected $description = 'Send a test HTTP request to a configured webhook endpoint.';

	public function handle(): int
	{
		if (Features::inactive('webhook')) {
			$this->warn('The webhook feature is disabled (WEBHOOK_ENABLED is not set to true).');
			$this->warn('Set WEBHOOK_ENABLED=true in your .env file and clear the config cache to enable it.');

			return self::FAILURE;
		}

		$id = $this->argument('id');

		/** @var Webhook|null $webhook */
		$webhook = Webhook::find($id);

		if ($webhook === null) {
			$this->error("Webhook not found: {$id}");

			return self::FAILURE;
		}

		$this->info("Testing webhook: {$webhook->name} ({$webhook->url})");

		// Build a synthetic payload with clearly labelled test data.
		$payload = new WebhookPayload(
			photo_id: 'TEST_PHOTO_ID',
			album_id: 'TEST_ALBUM_ID',
			title: 'Test Photo (lychee:webhook-test)',
			size_variants: [
				['type' => 'original', 'url' => 'https://example.com/test-original.jpg'],
				['type' => 'thumb', 'url' => 'https://example.com/test-thumb.jpg'],
			],
		);

		// Dispatch synchronously so the operator sees the outcome immediately.
		try {
			(new WebhookDispatchJob($webhook, $payload))->handle();
			$this->info('Webhook test dispatched. Check storage/logs/laravel.log for the result.');
		} catch (\Throwable $e) {
			$this->error('Webhook dispatch failed: ' . $e->getMessage());

			return self::FAILURE;
		}

		return self::SUCCESS;
	}
}
