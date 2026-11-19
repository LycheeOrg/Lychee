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

	private function addPhoto(Album $album, ?string $taken_at): void
	{
		Photo::factory()->without_size_variants()->owned_by($this->user)->in($album)->create([
			'taken_at' => $taken_at === null ? null : new Carbon($taken_at, 'UTC'),
		]);
	}

	public function testAlbumWithDatedPhotoAndNoRangeWarns(): void
	{
		$album = Album::factory()->as_root()->owned_by($this->user)->create();
		$this->addPhoto($album, '2023-01-15 10:00:00');

		$result = $this->runCheck();

		self::assertCount(1, $result);
		self::assertEquals(MessageType::WARNING, $result[0]->type);
		self::assertEquals(AlbumTakenAtRangeCheck::class, $result[0]->from);
		self::assertEquals('There are 1 albums with dated photos but no taken date range.', $result[0]->message);
		self::assertEquals(['Go to the maintenance page to recompute them, or run: php artisan lychee:recompute-album-stats'], $result[0]->details);
	}

	public function testRecomputedAlbumAddsNothing(): void
	{
		$album = Album::factory()->as_root()->owned_by($this->user)->create();
		$this->addPhoto($album, '2023-01-15 10:00:00');
		(new RecomputeAlbumStatsJob($album->id, propagate_to_parent: false))->handle();

		self::assertEmpty($this->runCheck());
	}

	public function testAlbumsWithoutDatedPhotosAddNothing(): void
	{
		Album::factory()->as_root()->owned_by($this->user)->create();
		$album = Album::factory()->as_root()->owned_by($this->user)->create();
		$this->addPhoto($album, null);

		self::assertEmpty($this->runCheck());
	}
}
