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

use App\Actions\Diagnostics\Pipes\Checks\WorkerCacheCheck;
use App\DTO\DiagnosticData;
use App\Enum\MessageType;
use Illuminate\Support\Facades\Config;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\AbstractTestCase;

class WorkerCacheCheckTest extends AbstractTestCase
{
	/**
	 * The LycheeOrg image markers live in the image root: the detection is
	 * overridden instead of creating them.
	 *
	 * @return DiagnosticData[]
	 */
	private function runCheck(bool $is_lychee_org_image): array
	{
		$check = new class($is_lychee_org_image) extends WorkerCacheCheck {
			public function __construct(private bool $is_lychee_org_image)
			{
			}

			protected function isLycheeOrgImage(): bool
			{
				return $this->is_lychee_org_image;
			}
		};
		$data = [];

		return $check->handle($data, fn (array $data) => $data);
	}

	public function testFileCacheWithAsynchronousQueueInLycheeOrgImageWarns(): void
	{
		Config::set('queue.default', 'database');
		Config::set('cache.default', 'file');

		$result = $this->runCheck(is_lychee_org_image: true);

		self::assertCount(1, $result);
		self::assertEquals(MessageType::WARNING, $result[0]->type);
		self::assertEquals(WorkerCacheCheck::class, $result[0]->from);
		self::assertStringContainsString('"file" cache', $result[0]->message);
		self::assertContains('Set CACHE_STORE=database or CACHE_STORE=redis in .env (both containers).', $result[0]->details);
	}

	#[DataProvider('sharedStoreProvider')]
	public function testSharedCacheAddsNothing(string $store): void
	{
		Config::set('queue.default', 'database');
		Config::set('cache.default', $store);

		self::assertEmpty($this->runCheck(is_lychee_org_image: true));
	}

	/**
	 * @return array<string,array{string}>
	 */
	public static function sharedStoreProvider(): array
	{
		return [
			'database' => ['database'],
			'redis' => ['redis'],
		];
	}

	public function testSyncQueueAddsNothing(): void
	{
		Config::set('queue.default', 'sync');
		Config::set('cache.default', 'file');

		self::assertEmpty($this->runCheck(is_lychee_org_image: true));
	}

	public function testOutsideLycheeOrgImageAddsNothing(): void
	{
		Config::set('queue.default', 'database');
		Config::set('cache.default', 'file');

		self::assertEmpty($this->runCheck(is_lychee_org_image: false));
	}
}
