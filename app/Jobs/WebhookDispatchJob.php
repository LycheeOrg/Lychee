<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Jobs;

use App\DTO\WebhookPayload;
use App\Enum\WebhookMethod;
use App\Enum\WebhookPayloadFormat;
use App\Models\Webhook;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use function Safe\json_encode;

/**
 * Dispatches a single outgoing HTTP request to a webhook endpoint.
 *
 * - No automatic retry (tries = 1, Q-031-04 → A).
 * - payload_format = json: payload sent as a JSON request body.
 * - payload_format = query_string: payload sent as URL query parameters;
 *   size_variant URLs are base64-encoded (Q-031-08 → base64).
 * - On non-2xx or exception: logs at ERROR level and discards.
 * - On success: logs at DEBUG level.
 */
class WebhookDispatchJob implements ShouldQueue
{
	use Dispatchable;
	use InteractsWithQueue;
	use Queueable;
	use SerializesModels;

	/** No automatic retry. */
	public int $tries = 1;

	public function __construct(
		public readonly Webhook $webhook,
		public readonly WebhookPayload $payload,
	) {
	}

	/**
	 * Execute the job.
	 */
	public function handle(): void
	{
		$timeout = (int) config('features.webhook_timeout_seconds', 10);
		$url = $this->webhook->url;
		$event_name = $this->webhook->event->value;

		// Build standard headers
		$headers = [
			'User-Agent' => 'Lychee/Webhooks',
			'X-Lychee-Event' => $event_name,
		];

		// Attach secret if configured
		$secret = $this->webhook->secret;
		if ($secret !== null && $secret !== '') {
			$header_name = ($this->webhook->secret_header !== null && $this->webhook->secret_header !== '')
				? $this->webhook->secret_header
				: 'X-Webhook-Secret';
			$headers[$header_name] = $secret;
		}

		try {
			$client = Http::withHeaders($headers)->timeout($timeout);

			if ($this->webhook->payload_format === WebhookPayloadFormat::JSON) {
				// Send JSON body regardless of HTTP method (operator's explicit choice)
				$client = $client->withBody(
					json_encode($this->payload->toJsonArray(), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
					'application/json',
				);
				$response = $this->sendRequest($client, $this->webhook->method, $url);
			} else {
				// Query-string: append params to URL
				$query = http_build_query($this->payload->toQueryArray());
				$separator = str_contains($url, '?') ? '&' : '?';
				$target_url = $query !== '' ? $url . $separator . $query : $url;
				$response = $this->sendRequest($client, $this->webhook->method, $target_url);
			}

			if ($response->successful()) {
				Log::debug('webhook.dispatch.success', [
					'webhook_id' => $this->webhook->id,
					'event' => $event_name,
					'method' => $this->webhook->method->value,
					'status_code' => $response->status(),
				]);
			} else {
				Log::error('webhook.dispatch.failure', [
					'webhook_id' => $this->webhook->id,
					'event' => $event_name,
					'method' => $this->webhook->method->value,
					'status_code' => $response->status(),
					'error' => $response->body(),
				]);
			}
		} catch (\Throwable $e) {
			Log::error('webhook.dispatch.failure', [
				'webhook_id' => $this->webhook->id,
				'event' => $event_name,
				'method' => $this->webhook->method->value,
				'error' => $e->getMessage(),
			]);
		}
	}

	/**
	 * Send an HTTP request using the configured method, avoiding dynamic method calls.
	 *
	 * @param PendingRequest $client
	 * @param WebhookMethod  $method
	 * @param string         $url
	 *
	 * @return \Illuminate\Http\Client\Response
	 */
	private function sendRequest(PendingRequest $client, WebhookMethod $method, string $url): \Illuminate\Http\Client\Response
	{
		return match ($method) {
			WebhookMethod::GET => $client->get($url),
			WebhookMethod::POST => $client->post($url),
			WebhookMethod::PUT => $client->put($url),
			WebhookMethod::PATCH => $client->patch($url),
			WebhookMethod::DELETE => $client->delete($url),
		};
	}
}
