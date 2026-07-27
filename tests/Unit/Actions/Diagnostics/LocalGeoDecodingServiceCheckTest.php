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

namespace Tests\Unit\Actions\Diagnostics;

use App\Actions\Diagnostics\Pipes\Checks\LocalGeoDecodingServiceCheck;
use App\Enum\MessageType;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Tests\AbstractTestCase;

/**
 * Unit tests for {@see LocalGeoDecodingServiceCheck}.
 */
class LocalGeoDecodingServiceCheckTest extends AbstractTestCase
{
	private \Closure $next;

	protected function setUp(): void
	{
		parent::setUp();

		$this->next = fn (array $data): array => $data;
	}

	public function tearDown(): void
	{
		\Mockery::close();
		parent::tearDown();
	}

	private function makeAdmin(): User
	{
		$user = \Mockery::mock(User::class)->makePartial();
		$user->may_administrate = true;

		return $user;
	}

	private function enableLocalService(string $base_url = 'http://local-geo:8080'): void
	{
		Config::set('features.v8', true);
		Config::set('services.local-geo-decoding.base_url', $base_url);
	}

	// ── skip conditions ─────────────────────────────────────────

	public function testNoEntriesWhenV8FeatureDisabled(): void
	{
		Config::set('features.v8', false);
		Config::set('services.local-geo-decoding.base_url', 'http://local-geo:8080');

		Http::fake();

		$data = [];
		$result = (new LocalGeoDecodingServiceCheck())->handle($data, $this->next);

		$this->assertSame([], $result);
		Http::assertNothingSent();
	}

	public function testNoEntriesWhenBaseUrlNotSet(): void
	{
		Config::set('features.v8', true);
		Config::set('services.local-geo-decoding.base_url', '');

		Http::fake();

		$data = [];
		$result = (new LocalGeoDecodingServiceCheck())->handle($data, $this->next);

		$this->assertSame([], $result);
		Http::assertNothingSent();
	}

	public function testNoEntriesWhenUserIsNotAdmin(): void
	{
		$this->enableLocalService();
		Auth::shouldReceive('user')->andReturn(null);

		Http::fake();

		$data = [];
		$result = (new LocalGeoDecodingServiceCheck())->handle($data, $this->next);

		$this->assertSame([], $result);
		Http::assertNothingSent();
	}

	// ── health check success ────────────────────────────────────

	public function testNoEntriesWhenHealthCheckReturns200(): void
	{
		$this->enableLocalService();
		Auth::shouldReceive('user')->andReturn($this->makeAdmin());

		Http::fake(['http://local-geo:8080/health' => Http::response(null, 200)]);

		$data = [];
		$result = (new LocalGeoDecodingServiceCheck())->handle($data, $this->next);

		$this->assertSame([], $result);
	}

	public function testNoEntriesWhenHealthCheckReturns204(): void
	{
		$this->enableLocalService();
		Auth::shouldReceive('user')->andReturn($this->makeAdmin());

		Http::fake(['http://local-geo:8080/health' => Http::response(null, 204)]);

		$data = [];
		$result = (new LocalGeoDecodingServiceCheck())->handle($data, $this->next);

		$this->assertSame([], $result);
	}

	// ── health check failures ───────────────────────────────────

	public function testErrorWhenHealthCheckReturnsNonSuccessStatus(): void
	{
		$this->enableLocalService();
		Auth::shouldReceive('user')->andReturn($this->makeAdmin());

		Http::fake(['http://local-geo:8080/health' => Http::response(null, 503)]);

		$data = [];
		$result = (new LocalGeoDecodingServiceCheck())->handle($data, $this->next);

		$this->assertCount(1, $result);
		$this->assertSame(MessageType::ERROR, $result[0]->type);
		$this->assertStringContainsString('503', $result[0]->message);
	}

	public function testErrorWhenConnectionFails(): void
	{
		$this->enableLocalService();
		Auth::shouldReceive('user')->andReturn($this->makeAdmin());

		Http::fake(function (): void {
			throw new \Illuminate\Http\Client\ConnectionException('Connection refused');
		});

		$data = [];
		$result = (new LocalGeoDecodingServiceCheck())->handle($data, $this->next);

		$this->assertCount(1, $result);
		$this->assertSame(MessageType::ERROR, $result[0]->type);
		$this->assertStringContainsString('Could not connect', $result[0]->message);
	}
}
