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

use App\Actions\Diagnostics\Pipes\Checks\RecomputeQueueCheck;
use App\DTO\DiagnosticData;
use App\Enum\MessageType;
use Illuminate\Support\Facades\Config;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\AbstractTestCase;

class RecomputeQueueCheckTest extends AbstractTestCase
{
	/**
	 * The LycheeOrg image markers live in the image root: the detection is
	 * overridden instead of creating them.
	 */
	private function makeCheck(bool $is_lychee_org_image): RecomputeQueueCheck
	{
		return new class($is_lychee_org_image) extends RecomputeQueueCheck {
			public function __construct(private bool $is_lychee_org_image)
			{
			}

			protected function isLycheeOrgImage(): bool
			{
				return $this->is_lychee_org_image;
			}
		};
	}

	/**
	 * @return DiagnosticData[]
	 */
	private function runCheck(bool $is_lychee_org_image): array
	{
		$data = [];

		return $this->makeCheck($is_lychee_org_image)->handle($data, fn (array $data) => $data);
	}

	public function testSyncConnectionAddsNothing(): void
	{
		Config::set('queue.default', 'sync');

		self::assertEmpty($this->runCheck(is_lychee_org_image: false));
	}

	public function testDatabaseConnectionOutsideLycheeOrgImageInforms(): void
	{
		Config::set('queue.default', 'database');
		Config::set('queue.connections.database.queue', 'default');

		$result = $this->runCheck(is_lychee_org_image: false);

		self::assertCount(1, $result);
		self::assertEquals(MessageType::INFO, $result[0]->type);
		self::assertEquals(RecomputeQueueCheck::class, $result[0]->from);
		self::assertStringContainsString('"low" queue', $result[0]->message);
		self::assertContains('Start the queue worker with: php artisan queue:work --queue=high,default,low', $result[0]->details);
	}

	/**
	 * Unset `QUEUE_NAMES`: the entrypoint default (`high,default,low`) applies.
	 */
	#[DataProvider('queueNamesWithLowProvider')]
	public function testLycheeOrgImageWhichProcessesLowAddsNothing(?string $queue_names): void
	{
		Config::set('queue.default', 'database');
		Config::set('queue.worker_queues', $queue_names);

		self::assertEmpty($this->runCheck(is_lychee_org_image: true));
	}

	/**
	 * @return array<string,array{string|null}>
	 */
	public static function queueNamesWithLowProvider(): array
	{
		return [
			'unset' => [null],
			'empty' => [''],
			'default,low' => ['default,low'],
			'high,default,low' => ['high,default,low'],
			'spaces' => [' default , low '],
		];
	}

	public function testLycheeOrgImageWhichSkipsLowWarns(): void
	{
		Config::set('queue.default', 'database');
		Config::set('queue.worker_queues', 'default');

		$result = $this->runCheck(is_lychee_org_image: true);

		self::assertCount(1, $result);
		self::assertEquals(MessageType::WARNING, $result[0]->type);
		self::assertEquals(RecomputeQueueCheck::class, $result[0]->from);
		self::assertStringContainsString('QUEUE_NAMES', $result[0]->message);
		self::assertContains('Set QUEUE_NAMES=default,low, or remove it to use the default (high,default,low).', $result[0]->details);
	}

	public function testSyncConnectionInsideLycheeOrgImageAddsNothing(): void
	{
		Config::set('queue.default', 'sync');
		Config::set('queue.worker_queues', 'default');

		self::assertEmpty($this->runCheck(is_lychee_org_image: true));
	}

	public function testCommandNamesTheDefaultQueueOfTheConnection(): void
	{
		Config::set('queue.default', 'redis');
		Config::set('queue.connections.redis.queue', 'lychee');

		$result = $this->runCheck(is_lychee_org_image: false);

		self::assertCount(1, $result);
		self::assertContains('Start the queue worker with: php artisan queue:work --queue=high,lychee,low', $result[0]->details);
	}
}
