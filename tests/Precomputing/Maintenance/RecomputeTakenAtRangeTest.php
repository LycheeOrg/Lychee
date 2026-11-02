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

namespace Tests\Precomputing\Maintenance;

use App\Jobs\RecomputeAlbumStatsJob;
use App\Models\Album;
use App\Models\Photo;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;
use Tests\Precomputing\Base\BasePrecomputingTest;

class RecomputeTakenAtRangeTest extends BasePrecomputingTest
{
	private User $user;

	public function setUp(): void
	{
		parent::setUp();
		$this->user = User::factory()->create();
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

	private function check(): int
	{
		$response = $this->actingAs($this->admin)->getJsonWithData('Maintenance::recomputeTakenAtRange');
		$this->assertOk($response);

		return $response->json();
	}

	public function testGuest(): void
	{
		$response = $this->getJsonWithData('Maintenance::recomputeTakenAtRange');
		$this->assertUnauthorized($response);

		$response = $this->postJson('Maintenance::recomputeTakenAtRange');
		$this->assertUnauthorized($response);
	}

	public function testUser(): void
	{
		$response = $this->actingAs($this->user)->getJsonWithData('Maintenance::recomputeTakenAtRange');
		$this->assertForbidden($response);

		$response = $this->actingAs($this->user)->postJson('Maintenance::recomputeTakenAtRange');
		$this->assertForbidden($response);
	}

	/**
	 * `photos.taken_at` keeps the microseconds the album columns drop.
	 */
	public function testRangeWrittenByTheJobIsNotCounted(): void
	{
		$parent = Album::factory()->as_root()->owned_by($this->user)->create();
		$child = Album::factory()->children_of($parent)->owned_by($this->user)->create();
		$this->addPhoto($parent, '2023-01-15 10:00:00.250000');
		$this->addPhoto($child, '2024-06-20 14:30:00.750000');
		$this->recompute($child);
		$this->recompute($parent);

		self::assertSame(0, $this->check());
	}

	public function testAlbumsWithoutTakenDatesAreNotCounted(): void
	{
		Album::factory()->as_root()->owned_by($this->user)->create();
		$album = Album::factory()->as_root()->owned_by($this->user)->create();
		$this->addPhoto($album, null);

		self::assertSame(0, $this->check());
	}

	public function testMissingRangeIsCounted(): void
	{
		$album = Album::factory()->as_root()->owned_by($this->user)->create();
		$this->addPhoto($album, '2023-01-15 10:00:00');

		self::assertSame(1, $this->check());
	}

	public function testWrongMinimumIsCounted(): void
	{
		$album = Album::factory()->as_root()->owned_by($this->user)->create();
		$this->addPhoto($album, '2023-01-15 10:00:00');
		$this->addPhoto($album, '2024-06-20 14:30:00');
		$this->recompute($album);
		DB::table('albums')->where('id', '=', $album->id)->update(['min_taken_at' => '2023-01-15 10:00:01']);

		self::assertSame(1, $this->check());
	}

	public function testDoQueuesOnlyTheStaleAlbums(): void
	{
		$parent = Album::factory()->as_root()->owned_by($this->user)->create();
		$child = Album::factory()->children_of($parent)->owned_by($this->user)->create();
		$this->addPhoto($child, '2023-01-15 10:00:00');
		$this->recompute($child);
		$this->recompute($parent);
		$this->addPhoto($child, '2025-03-01 08:00:00');
		$this->recompute($child);

		Queue::fake();
		$response = $this->actingAs($this->admin)->postJson('Maintenance::recomputeTakenAtRange');
		$this->assertNoContent($response);

		Queue::assertPushed(RecomputeAlbumStatsJob::class, 1);
		Queue::assertPushed(RecomputeAlbumStatsJob::class, fn (RecomputeAlbumStatsJob $job) => $job->album_id === $parent->id && $job->propagate_to_parent === false);
	}

	public function testDoRecomputesTheStaleRanges(): void
	{
		$album = Album::factory()->as_root()->owned_by($this->user)->create();
		$this->addPhoto($album, '2023-01-15 10:00:00');
		$this->addPhoto($album, '2024-06-20 14:30:00');
		self::assertSame(1, $this->check());

		$response = $this->actingAs($this->admin)->postJson('Maintenance::recomputeTakenAtRange');
		$this->assertNoContent($response);

		self::assertSame(0, $this->check());
		$album->refresh();
		self::assertEquals('2023-01-15 10:00:00', $album->min_taken_at->format('Y-m-d H:i:s'));
		self::assertEquals('2024-06-20 14:30:00', $album->max_taken_at->format('Y-m-d H:i:s'));
	}
}
