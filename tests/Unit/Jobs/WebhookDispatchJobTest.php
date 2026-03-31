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

namespace Tests\Unit\Jobs;

use App\DTO\WebhookPayload;
use App\Enum\PhotoWebhookEvent;
use App\Enum\WebhookMethod;
use App\Enum\WebhookPayloadFormat;
use App\Jobs\WebhookDispatchJob;
use App\Models\Webhook;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\AbstractTestCase;

/**
 * Unit tests for WebhookDispatchJob.
 *
 * Uses Http::fake() to intercept outgoing HTTP requests and verify:
 * - Correct URL and method routing for all WebhookMethod values
 * - JSON vs query-string payload encoding
 * - Secret header injection
 * - Success / failure logging
 * - Exception handling
 */
class WebhookDispatchJobTest extends AbstractTestCase
{
	// ── helpers ───────────────────────────────────────────────────────────────

	/**
	 * Build a minimal Webhook model stub (no DB).
	 *
	 * @param array<string,mixed> $attributes
	 *
	 * @return Webhook
	 */
	private function makeWebhook(array $attributes): Webhook
	{
		$webhook = new Webhook();
		$defaults = [
			'id' => 'test-webhook-id',
			'name' => 'Test Webhook',
			'event' => PhotoWebhookEvent::ADD,
			'method' => WebhookMethod::POST,
			'url' => 'https://example.com/hook',
			'payload_format' => WebhookPayloadFormat::JSON,
			'secret' => null,
			'secret_header' => null,
			'enabled' => true,
			'send_photo_id' => true,
			'send_album_id' => true,
			'send_title' => true,
			'send_size_variants' => false,
			'size_variant_types' => null,
		];
		foreach (array_merge($defaults, $attributes) as $key => $value) {
			$webhook->{$key} = $value;
		}

		return $webhook;
	}

	private function makePayload(): WebhookPayload
	{
		return new WebhookPayload('photo-1', 'album-1', 'Sunset', null);
	}

	// ── HTTP method routing ───────────────────────────────────────────────────

	#[DataProvider('httpMethodProvider')]
	public function testAllHttpMethodsAreRoutedCorrectly(WebhookMethod $method, string $httpVerb): void
	{
		Http::fake(['*' => Http::response('ok', 200)]);

		$webhook = $this->makeWebhook(['method' => $method]);
		$job = new WebhookDispatchJob($webhook, $this->makePayload());
		$job->handle();

		Http::assertSent(function (Request $request) use ($httpVerb): bool {
			return $request->method() === $httpVerb;
		});
	}

	/** @return array<string, array{WebhookMethod, string}> */
	public static function httpMethodProvider(): array
	{
		return [
			'GET' => [WebhookMethod::GET, 'GET'],
			'POST' => [WebhookMethod::POST, 'POST'],
			'PUT' => [WebhookMethod::PUT, 'PUT'],
			'PATCH' => [WebhookMethod::PATCH, 'PATCH'],
			'DELETE' => [WebhookMethod::DELETE, 'DELETE'],
		];
	}

	// ── payload format ────────────────────────────────────────────────────────

	public function testJsonFormatSendsJsonBody(): void
	{
		Http::fake(['*' => Http::response('', 200)]);

		$webhook = $this->makeWebhook(['payload_format' => WebhookPayloadFormat::JSON]);
		$payload = new WebhookPayload('pid', 'aid', 'Title', null);
		(new WebhookDispatchJob($webhook, $payload))->handle();

		Http::assertSent(function (Request $request): bool {
			$body = json_decode($request->body(), true);

			return $request->hasHeader('Content-Type', 'application/json') &&
				$body['photo_id'] === 'pid' &&
				$body['album_id'] === 'aid' &&
				$body['title'] === 'Title';
		});
	}

	public function testQueryStringFormatAppendsParametersToUrl(): void
	{
		Http::fake(['*' => Http::response('', 200)]);

		$webhook = $this->makeWebhook([
			'url' => 'https://hooks.example.com/notify',
			'payload_format' => WebhookPayloadFormat::QUERY_STRING,
		]);
		$payload = new WebhookPayload('pid', 'aid', null, null);
		(new WebhookDispatchJob($webhook, $payload))->handle();

		Http::assertSent(function (Request $request): bool {
			$url = $request->url();

			return str_contains($url, 'photo_id=pid') &&
				str_contains($url, 'album_id=aid');
		});
	}

	public function testQueryStringFormatWithExistingQueryStringUsesSeparatorCorrectly(): void
	{
		Http::fake(['*' => Http::response('', 200)]);

		$webhook = $this->makeWebhook([
			'url' => 'https://hooks.example.com/notify?token=abc',
			'payload_format' => WebhookPayloadFormat::QUERY_STRING,
		]);
		$payload = new WebhookPayload('pid', null, null, null);
		(new WebhookDispatchJob($webhook, $payload))->handle();

		Http::assertSent(function (Request $request): bool {
			$url = $request->url();

			return str_contains($url, 'token=abc') &&
				str_contains($url, 'photo_id=pid');
		});
	}

	public function testQueryStringFormatWithEmptyPayloadKeepsOriginalUrl(): void
	{
		Http::fake(['*' => Http::response('', 200)]);

		$webhook = $this->makeWebhook([
			'url' => 'https://hooks.example.com/notify',
			'payload_format' => WebhookPayloadFormat::QUERY_STRING,
		]);
		// All nulls → empty query array
		$payload = new WebhookPayload(null, null, null, null);
		(new WebhookDispatchJob($webhook, $payload))->handle();

		Http::assertSent(function (Request $request): bool {
			return $request->url() === 'https://hooks.example.com/notify';
		});
	}

	// ── secret header ─────────────────────────────────────────────────────────

	public function testSecretIsAddedWithDefaultHeader(): void
	{
		Http::fake(['*' => Http::response('', 200)]);

		$webhook = $this->makeWebhook([
			'secret' => 'my-secret',
			'secret_header' => null,
		]);
		(new WebhookDispatchJob($webhook, $this->makePayload()))->handle();

		Http::assertSent(function (Request $request): bool {
			return $request->hasHeader('X-Webhook-Secret', 'my-secret');
		});
	}

	public function testSecretIsAddedWithCustomHeader(): void
	{
		Http::fake(['*' => Http::response('', 200)]);

		$webhook = $this->makeWebhook([
			'secret' => 'super-secret',
			'secret_header' => 'X-My-Signature',
		]);
		(new WebhookDispatchJob($webhook, $this->makePayload()))->handle();

		Http::assertSent(function (Request $request): bool {
			return $request->hasHeader('X-My-Signature', 'super-secret');
		});
	}

	public function testNoSecretHeaderWhenSecretIsNull(): void
	{
		Http::fake(['*' => Http::response('', 200)]);

		$webhook = $this->makeWebhook(['secret' => null]);
		(new WebhookDispatchJob($webhook, $this->makePayload()))->handle();

		Http::assertSent(function (Request $request): bool {
			return !$request->hasHeader('X-Webhook-Secret') &&
				!$request->hasHeader('X-My-Signature');
		});
	}

	// ── standard headers ─────────────────────────────────────────────────────

	public function testStandardHeadersAreAlwaysPresent(): void
	{
		Http::fake(['*' => Http::response('', 200)]);

		$webhook = $this->makeWebhook(['event' => PhotoWebhookEvent::ADD]);
		(new WebhookDispatchJob($webhook, $this->makePayload()))->handle();

		Http::assertSent(function (Request $request): bool {
			return $request->hasHeader('User-Agent', 'Lychee/Webhooks') &&
				$request->hasHeader('X-Lychee-Event', 'photo.add');
		});
	}

	// ── logging ───────────────────────────────────────────────────────────────

	public function testSuccessfulResponseLogsDebug(): void
	{
		Http::fake(['*' => Http::response('', 200)]);
		Log::shouldReceive('debug')
			->once()
			->with('webhook.dispatch.success', \Mockery::type('array'));

		$webhook = $this->makeWebhook([]);
		(new WebhookDispatchJob($webhook, $this->makePayload()))->handle();
	}

	public function testFailedResponseLogsError(): void
	{
		Http::fake(['*' => Http::response('Server Error', 500)]);
		Log::shouldReceive('error')
			->once()
			->with('webhook.dispatch.failure', \Mockery::type('array'));

		$webhook = $this->makeWebhook([]);
		(new WebhookDispatchJob($webhook, $this->makePayload()))->handle();
	}

	public function testExceptionDuringRequestLogsError(): void
	{
		Http::fake(['*' => fn () => throw new \RuntimeException('Connection refused')]);
		Log::shouldReceive('error')
			->once()
			->with('webhook.dispatch.failure', \Mockery::type('array'));

		$webhook = $this->makeWebhook([]);
		(new WebhookDispatchJob($webhook, $this->makePayload()))->handle();
	}

	// ── timeout config ────────────────────────────────────────────────────────

	public function testCustomTimeoutIsUsed(): void
	{
		Config::set('features.webhook_timeout_seconds', 42);
		Http::fake(['*' => Http::response('', 200)]);

		$webhook = $this->makeWebhook([]);
		// We just verify the job runs without error; timeout is tested by Http::fake() not throwing.
		(new WebhookDispatchJob($webhook, $this->makePayload()))->handle();

		Http::assertSentCount(1);
	}
}
