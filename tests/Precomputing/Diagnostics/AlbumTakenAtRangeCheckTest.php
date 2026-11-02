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

use App\Actions\Diagnostics\Pipes\Checks\AlbumTakenAtRangeCheck;
use App\DTO\DiagnosticData;
use App\Enum\MessageType;
use App\Jobs\RecomputeAlbumStatsJob;
use App\Models\Album;
use App\Models\Photo;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Tests\Precomputing\Base\BasePrecomputingTest;

class AlbumTakenAtRangeCheckTest extends BasePrecomputingTest
{
	private User $user;

	public function setUp(): void
	{
		parent::setUp();
		$this->user = User::factory()->create();
	}

	/**
	 * @return DiagnosticData[]
	 */
	private function runCheck(): array
	{
		$data = [];

		return (new AlbumTakenAtRangeCheck())->handle($data, fn (array $data) => $data);
	}

	private function recompute(Album $album): void
	{
		(new RecomputeAlbumStatsJob($album->id, propagate_to_parent: false))->handle();
	}

	private function addPhoto(Album $album, ?string $taken_at): void
	{
		Photo::factory()->without_size_variants()->owned_by($this->user)->in($album)->create([
			'taken_at' => $taken_at === null ? null : new Carbon($taken_at, 'UTC'),
		]);
	}

	/**
	 * `photos.taken_at` keeps the microseconds the album columns drop.
	 */
	public function testRangeWrittenByTheJobAddsNothing(): void
	{
		$parent = Album::factory()->as_root()->owned_by($this->user)->create();
		$child = Album::factory()->children_of($parent)->owned_by($this->user)->create();
		$this->addPhoto($parent, '2023-01-15 10:00:00.250000');
		$this->addPhoto($child, '2024-06-20 14:30:00.750000');
		$this->recompute($child);
		$this->recompute($parent);

		self::assertEmpty($this->runCheck());
	}

	public function testAlbumsWithoutTakenDatesAddNothing(): void
	{
		Album::factory()->as_root()->owned_by($this->user)->create();
		$album = Album::factory()->as_root()->owned_by($this->user)->create();
		$this->addPhoto($album, null);

		self::assertEmpty($this->runCheck());
	}

	public function testMissingRangeWarns(): void
	{
		$album = Album::factory()->as_root()->owned_by($this->user)->create();
		$this->addPhoto($album, '2023-01-15 10:00:00');

		$result = $this->runCheck();

		self::assertCount(1, $result);
		self::assertEquals(MessageType::WARNING, $result[0]->type);
		self::assertEquals(AlbumTakenAtRangeCheck::class, $result[0]->from);
		self::assertEquals('There are 1 albums whose taken date range does not match their photos.', $result[0]->message);
		self::assertEquals(['Albums sorted by taken date are misplaced. Run: php artisan lychee:recompute-album-stats'], $result[0]->details);
		self::assertStringNotContainsString($album->id, $result[0]->message);
	}

	public function testParentMissingANewerChildPhotoIsStale(): void
	{
		$parent = Album::factory()->as_root()->owned_by($this->user)->create();
		$child = Album::factory()->children_of($parent)->owned_by($this->user)->create();
		$this->addPhoto($child, '2023-01-15 10:00:00');
		$this->recompute($child);
		$this->recompute($parent);
		$this->addPhoto($child, '2025-03-01 08:00:00');
		$this->recompute($child);

		self::assertSame([$parent->id], (new AlbumTakenAtRangeCheck())->getAlbumIdsWithStaleRange());
	}

	public function testWrongMinimumIsStale(): void
	{
		$album = Album::factory()->as_root()->owned_by($this->user)->create();
		$this->addPhoto($album, '2023-01-15 10:00:00');
		$this->addPhoto($album, '2024-06-20 14:30:00');
		$this->recompute($album);
		DB::table('albums')->where('id', '=', $album->id)->update(['min_taken_at' => '2023-01-15 10:00:01']);

		self::assertSame([$album->id], (new AlbumTakenAtRangeCheck())->getAlbumIdsWithStaleRange());
	}
}
