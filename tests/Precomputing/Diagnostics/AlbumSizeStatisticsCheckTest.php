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

namespace Tests\Precomputing\Diagnostics;

use App\Actions\Diagnostics\Pipes\Checks\AlbumSizeStatisticsCheck;
use App\DTO\DiagnosticData;
use App\Enum\MessageType;
use App\Jobs\RecomputeAlbumSizeJob;
use App\Models\Album;
use App\Models\User;
use Tests\Precomputing\Base\BasePrecomputingTest;

class AlbumSizeStatisticsCheckTest extends BasePrecomputingTest
{
	/**
	 * @return DiagnosticData[]
	 */
	private function runCheck(): array
	{
		$data = [];

		return (new AlbumSizeStatisticsCheck())->handle($data, fn (array $data) => $data);
	}

	public function testAlbumsWithSizeStatisticsAddNothing(): void
	{
		$user = User::factory()->create();
		$album = Album::factory()->as_root()->owned_by($user)->create();
		(new RecomputeAlbumSizeJob($album->id, false))->handle();

		self::assertEmpty($this->runCheck());
	}

	public function testAlbumWithoutSizeStatisticsWarns(): void
	{
		$user = User::factory()->create();
		$parent = Album::factory()->as_root()->owned_by($user)->create();
		Album::factory()->children_of($parent)->owned_by($user)->create();
		(new RecomputeAlbumSizeJob($parent->id, false))->handle();

		$result = $this->runCheck();

		self::assertCount(1, $result);
		self::assertEquals(MessageType::WARNING, $result[0]->type);
		self::assertEquals(AlbumSizeStatisticsCheck::class, $result[0]->from);
		self::assertEquals('There are 1 albums without size statistics.', $result[0]->message);
		self::assertContains('Go to the maintenance page to compute the album sizes, or run: php artisan lychee:recompute-album-sizes', $result[0]->details);
	}
}
