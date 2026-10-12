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

namespace Tests\Precomputing\Dispatch;

use App\Enum\SmartAlbumType;
use App\Events\AlbumComputedDataUpdated;
use App\Events\PhotoSaved;
use App\Jobs\RecomputeAlbumPhotoBucketsJob;
use App\Jobs\RecomputeAlbumSizeJob;
use App\Jobs\RecomputeAlbumStatsJob;
use App\Jobs\RecomputeAlbumUserThumbsJob;
use App\Jobs\RecomputeChildAlbumBucketsJob;
use App\Jobs\RecomputePersonStatsJob;
use App\Jobs\RecomputePhotoBucketsJob;
use App\Jobs\RecomputeRootAlbumBucketsJob;
use App\Models\Album;
use App\Models\Photo;
use App\Models\User;
use Illuminate\Bus\UniqueLock;
use Illuminate\Cache\DatabaseStore;
use Illuminate\Contracts\Queue\ShouldBeUniqueUntilProcessing;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Queue;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Precomputing\Base\BasePrecomputingTest;

/**
 * Feature 090: a recompute job is pushed only when no copy for the same
 * target already waits in the queue.
 *
 * `Queue::fake()` never processes the pushed jobs, so their unique locks stay
 * taken for the rest of the test: a later dispatch for the same key is dropped.
 */
class RecomputeJobDispatchDedupTest extends BasePrecomputingTest
{
	/**
	 * Jobs keyed on one target id.
	 *
	 * @return array<string,array{class-string,\Closure(string):object}>
	 */
	public static function keyedJobProvider(): array
	{
		return [
			'album stats' => [RecomputeAlbumStatsJob::class, fn (string $id) => new RecomputeAlbumStatsJob($id)],
			'album size' => [RecomputeAlbumSizeJob::class, fn (string $id) => new RecomputeAlbumSizeJob($id)],
			'album user thumbs' => [RecomputeAlbumUserThumbsJob::class, fn (string $id) => new RecomputeAlbumUserThumbsJob(RecomputeAlbumUserThumbsJob::KIND_TAG, $id)],
			'photo buckets' => [RecomputePhotoBucketsJob::class, fn (string $id) => new RecomputePhotoBucketsJob($id)],
			'album photo buckets' => [RecomputeAlbumPhotoBucketsJob::class, fn (string $id) => new RecomputeAlbumPhotoBucketsJob($id)],
			'child album buckets' => [RecomputeChildAlbumBucketsJob::class, fn (string $id) => new RecomputeChildAlbumBucketsJob($id)],
		];
	}

	/**
	 * Every recompute job in scope.
	 *
	 * @return array<string,array{class-string,\Closure(string):object}>
	 */
	public static function jobProvider(): array
	{
		return array_merge(self::keyedJobProvider(), [
			'root album buckets' => [RecomputeRootAlbumBucketsJob::class, fn (string $id) => new RecomputeRootAlbumBucketsJob()],
		]);
	}

	/**
	 * Jobs propagating to the parent album.
	 *
	 * @return array<string,array{class-string}>
	 */
	public static function propagatingJobProvider(): array
	{
		return [
			'album stats' => [RecomputeAlbumStatsJob::class],
			'album size' => [RecomputeAlbumSizeJob::class],
		];
	}

	#[DataProvider('jobProvider')]
	public function testJobIsUniqueUntilProcessingForOneHour(string $class, \Closure $make): void
	{
		$job = $make('target-a');

		self::assertInstanceOf(ShouldBeUniqueUntilProcessing::class, $job);
		self::assertSame(3600, $job->uniqueFor);
	}

	/**
	 * The web container takes the lock, the worker container releases it:
	 * on an asynchronous queue both must use the database.
	 */
	#[DataProvider('jobProvider')]
	public function testUniqueLockLivesInTheDatabaseOnAsynchronousQueues(string $class, \Closure $make): void
	{
		Config::set('queue.default', 'database');

		self::assertInstanceOf(DatabaseStore::class, $make('target-a')->uniqueVia()->getStore());
	}

	#[DataProvider('jobProvider')]
	public function testUniqueLockLivesInTheDefaultStoreOnSync(string $class, \Closure $make): void
	{
		Config::set('queue.default', 'sync');

		self::assertSame(Cache::store(), $make('target-a')->uniqueVia());
	}

	/**
	 * The literal queue name is the contract: workers are started with
	 * `--queue=default,low`.
	 */
	#[DataProvider('jobProvider')]
	public function testJobIsPushedOnTheLowQueue(string $class, \Closure $make): void
	{
		Queue::fake();

		dispatch($make('target-a'));

		Queue::assertPushedOn('low', $class);
	}

	#[DataProvider('jobProvider')]
	public function testRepeatedDispatchPushesOneJob(string $class, \Closure $make): void
	{
		Queue::fake();

		dispatch($make('target-a'));
		dispatch($make('target-a'));
		dispatch($make('target-a'));

		Queue::assertPushed($class, 1);
	}

	#[DataProvider('keyedJobProvider')]
	public function testDifferentTargetsPushOneJobEach(string $class, \Closure $make): void
	{
		Queue::fake();

		dispatch($make('target-a'));
		dispatch($make('target-b'));
		dispatch($make('target-a'));

		Queue::assertPushed($class, 2);
	}

	#[DataProvider('propagatingJobProvider')]
	public function testPropagationFlagIsPartOfTheKey(string $class): void
	{
		Queue::fake();

		dispatch(new $class('target-a', true));
		dispatch(new $class('target-a', false));
		dispatch(new $class('target-a', true));
		dispatch(new $class('target-a', false));

		Queue::assertPushed($class, 2);
		Queue::assertPushed($class, fn ($job) => $job->propagate_to_parent === true);
		Queue::assertPushed($class, fn ($job) => $job->propagate_to_parent === false);
	}

	#[DataProvider('jobProvider')]
	public function testDispatchAfterTheJobRanIsPushed(string $class, \Closure $make): void
	{
		// Default `sync` connection: the job runs right away and releases its lock.
		dispatch($make('target-a'));

		Queue::fake();
		dispatch($make('target-a'));

		Queue::assertPushed($class, 1);
	}

	public function testLockIsFreeWhileTheJobRuns(): void
	{
		$album = Album::factory()->as_root()->owned_by($this->admin)->create();
		$job = new RecomputeAlbumStatsJob($album->id, false);
		$key = UniqueLock::getKey($job);

		$acquired_during_run = null;
		Event::listen(AlbumComputedDataUpdated::class, function () use ($key, &$acquired_during_run): void {
			$lock = Cache::lock($key, 10);
			$acquired_during_run = $lock->get();
			if ($acquired_during_run) {
				$lock->release();
			}
		});

		dispatch($job);

		self::assertTrue($acquired_during_run);
	}

	#[DataProvider('propagatingJobProvider')]
	public function testChildrenFinishingTogetherPushOneParentJob(string $class): void
	{
		$user = User::factory()->create();
		$parent = Album::factory()->as_root()->owned_by($user)->create();
		$child1 = Album::factory()->children_of($parent)->owned_by($user)->create();
		$child2 = Album::factory()->children_of($parent)->owned_by($user)->create();

		Queue::fake();

		(new $class($child1->id))->handle();
		(new $class($child2->id))->handle();

		Queue::assertPushed($class, 1);
		Queue::assertPushed($class, fn ($job) => $job->album_id === $parent->id);
	}

	/**
	 * Real `database` queue drained by a real worker: the upload jobs (stood in
	 * by a non-unique job on the default queue) run first, the single waiting
	 * recompute runs after them, and a dispatch after it ran is queued again.
	 */
	public function testDatabaseQueueRunsOneRecomputeAfterTheUploads(): void
	{
		$album = Album::factory()->as_root()->owned_by($this->admin)->create();
		Config::set('queue.default', 'database');

		dispatch(new RecomputePersonStatsJob([]));
		RecomputeAlbumStatsJob::dispatch($album->id);
		dispatch(new RecomputePersonStatsJob([]));
		RecomputeAlbumStatsJob::dispatch($album->id);

		self::assertSame(2, $this->countQueuedJobs('default'));
		self::assertSame(1, $this->countQueuedJobs('low'));
		self::assertSame(1, DB::table('cache_locks')->count());

		$this->runWorkerOnce();
		$this->runWorkerOnce();
		self::assertSame(0, $this->countQueuedJobs('default'));
		self::assertSame(1, $this->countQueuedJobs('low'));
		self::assertSame(1, DB::table('cache_locks')->count());

		$this->runWorkerOnce();
		self::assertSame(0, $this->countQueuedJobs('low'));
		self::assertSame(0, DB::table('cache_locks')->count());

		RecomputeAlbumStatsJob::dispatch($album->id);
		self::assertSame(1, $this->countQueuedJobs('low'));
		self::assertSame(1, DB::table('cache_locks')->count());
	}

	private function countQueuedJobs(string $queue): int
	{
		return DB::table('jobs')->where('queue', '=', $queue)->count();
	}

	private function runWorkerOnce(): void
	{
		Artisan::call('queue:work', ['connection' => 'database', '--queue' => 'default,low', '--once' => true]);
	}

	public function testTwoUploadsIntoOneAlbumPushOneRecomputePerJob(): void
	{
		$user = User::factory()->create();
		$album = Album::factory()->as_root()->owned_by($user)->create();
		$photo1 = Photo::factory()->owned_by($user)->create();
		$photo2 = Photo::factory()->owned_by($user)->create();
		$photo1->albums()->attach([$album->id]);
		$photo2->albums()->attach([$album->id]);

		Queue::fake();

		// What the `SetParent` pipe fires at the end of each upload.
		PhotoSaved::dispatch([$photo1->id]);
		PhotoSaved::dispatch([$photo2->id]);

		Queue::assertPushed(RecomputeAlbumStatsJob::class, 1);
		Queue::assertPushed(RecomputeAlbumSizeJob::class, 1);
		Queue::assertPushed(RecomputeAlbumUserThumbsJob::class, count(SmartAlbumType::cases()));

		Queue::assertPushedOn('low', RecomputeAlbumStatsJob::class);
		Queue::assertPushedOn('low', RecomputeAlbumSizeJob::class);
		Queue::assertNotPushed(RecomputeAlbumUserThumbsJob::class, fn ($job, $queue) => $queue !== 'low');
	}
}
