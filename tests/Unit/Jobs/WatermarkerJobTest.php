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

use App\Enum\JobStatus;
use App\Image\Watermarker;
use App\Jobs\WatermarkerJob;
use App\Models\JobHistory;
use App\Models\Photo;
use App\Models\SizeVariant;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Log;
use Tests\AbstractTestCase;

class WatermarkerJobTest extends AbstractTestCase
{
	use DatabaseTransactions;

	private function makeJob(SizeVariant $variant, int $owner_id, Watermarker $watermarker): WatermarkerJob
	{
		return new class($variant, $owner_id, $watermarker) extends WatermarkerJob {
			public function __construct(SizeVariant $variant, int $owner_id, private Watermarker $watermarker)
			{
				parent::__construct($variant, $owner_id);
			}

			protected function getWatermarker(): Watermarker
			{
				return $this->watermarker;
			}
		};
	}

	public function testUniqueIdIncludesVariantId(): void
	{
		$user = User::factory()->create();
		$photo = Photo::factory()->owned_by($user)->create();
		$variant = SizeVariant::where('photo_id', $photo->id)->first();

		$job = $this->makeJob($variant, $user->id, \Mockery::mock(Watermarker::class));

		self::assertEquals('watermark:' . $variant->id, $job->uniqueId());
	}

	public function testSkipsWhenAlreadyWatermarked(): void
	{
		$user = User::factory()->create();
		$photo = Photo::factory()->owned_by($user)->create();
		$variant = SizeVariant::where('photo_id', $photo->id)->first();
		$variant->short_path_watermarked = 'watermarked/foo.jpg';
		$variant->save();

		$watermarker = \Mockery::mock(Watermarker::class);
		$watermarker->shouldNotReceive('do');

		$job = $this->makeJob($variant, $user->id, $watermarker);
		$job->handle();

		$history = JobHistory::query()->where('owner_id', '=', $user->id)->latest()->first();
		self::assertEquals(JobStatus::SUCCESS, $history->status);
	}

	public function testMarksSuccessWhenWatermarkApplied(): void
	{
		$user = User::factory()->create();
		$photo = Photo::factory()->owned_by($user)->create();
		$variant = SizeVariant::where('photo_id', $photo->id)->first();

		$watermarker = \Mockery::mock(Watermarker::class);
		$watermarker->shouldReceive('do')->once()->with(\Mockery::on(function (SizeVariant $v) use ($variant) {
			// The watermarker is expected to actually apply the mark; we simulate
			// that side effect here since the mock replaces the real implementation.
			SizeVariant::where('id', $variant->id)->update(['short_path_watermarked' => 'watermarked/foo.jpg']);

			return $v->id === $variant->id;
		}));

		$job = $this->makeJob($variant, $user->id, $watermarker);
		$job->handle();

		$history = JobHistory::query()->where('owner_id', '=', $user->id)->latest()->first();
		self::assertEquals(JobStatus::SUCCESS, $history->status);
	}

	public function testMarksFailureWhenWatermarkNotApplied(): void
	{
		$user = User::factory()->create();
		$photo = Photo::factory()->owned_by($user)->create();
		$variant = SizeVariant::where('photo_id', $photo->id)->first();

		$watermarker = \Mockery::mock(Watermarker::class);
		// Simulates a no-op watermarker: `do()` is called but leaves the variant unwatermarked.
		$watermarker->shouldReceive('do')->once();

		$job = $this->makeJob($variant, $user->id, $watermarker);
		$job->handle();

		$history = JobHistory::query()->where('owner_id', '=', $user->id)->latest()->first();
		self::assertEquals(JobStatus::FAILURE, $history->status);
	}

	public function testFailedMarksHistoryAndLogs(): void
	{
		$user = User::factory()->create();
		$photo = Photo::factory()->owned_by($user)->create();
		$variant = SizeVariant::where('photo_id', $photo->id)->first();

		Log::shouldReceive('channel')->once()->with('jobs')->andReturnSelf();
		Log::shouldReceive('error')->once();

		$job = $this->makeJob($variant, $user->id, \Mockery::mock(Watermarker::class));
		$job->failed(new \Exception('boom'));

		$history = JobHistory::query()->where('owner_id', '=', $user->id)->latest()->first();
		self::assertEquals(JobStatus::FAILURE, $history->status);
	}

	public function testFailedReleasesJobWhenExceptionCodeIs999(): void
	{
		$user = User::factory()->create();
		$photo = Photo::factory()->owned_by($user)->create();
		$variant = SizeVariant::where('photo_id', $photo->id)->first();

		Log::shouldReceive('channel')->never();

		$job = $this->makeJob($variant, $user->id, \Mockery::mock(Watermarker::class));
		// No underlying queue `$job` is bound, so `release()` is a safe no-op.
		$job->failed(new \Exception('retry me', 999));

		$history = JobHistory::query()->where('owner_id', '=', $user->id)->latest()->first();
		self::assertEquals(JobStatus::FAILURE, $history->status);
	}

	public function testGetWatermarkerResolvesFromContainer(): void
	{
		// With watermarking disabled, the real `Watermarker::do()` returns
		// immediately without touching any image file, so it is safe to use
		// the real (non-overridden) `WatermarkerJob::getWatermarker()` here.
		\App\Models\Configs::set('watermark_enabled', '0');

		$user = User::factory()->create();
		$photo = Photo::factory()->owned_by($user)->create();
		$variant = SizeVariant::where('photo_id', $photo->id)->first();

		$job = new WatermarkerJob($variant, $user->id);
		$job->handle();

		$history = JobHistory::query()->where('owner_id', '=', $user->id)->latest()->first();
		self::assertEquals(JobStatus::FAILURE, $history->status);
	}
}
