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

namespace Tests\Feature_v2\Photo;

use App\Jobs\RecomputeAlbumPhotoBucketsJob;
use App\Jobs\RecomputePhotoBucketsJob;
use Illuminate\Support\Facades\Queue;
use Tests\Feature_v2\Base\BaseApiWithDataTest;

/**
 * Covers the write sites that must dispatch
 * {@see RecomputePhotoBucketsJob}/{@see RecomputeAlbumPhotoBucketsJob} when
 * a photo's own bucket-relevant columns change, or when an album's own
 * *photo*-sort settings change — and must NOT dispatch for an unrelated
 * attribute change. Mirrors `Tests\Feature_v2\Album\AlbumSortingBucketDispatchTest`.
 */
class PhotoSortingBucketDispatchTest extends BaseApiWithDataTest
{
	// ── PhotoController::update() ──────────────────────────────────

	public function testUpdateTitleChangeDispatches(): void
	{
		Queue::fake([RecomputePhotoBucketsJob::class]);

		$response = $this->actingAs($this->userMayUpload1)->patchJson('Photo', [
			'photo_id' => $this->photo1->id,
			'title' => 'a brand new title',
			'description' => '',
			'tags' => [],
			'license' => 'none',
			'taken_at' => null,
			'upload_date' => '2021-01-01',
			'from_id' => $this->album1->id,
		]);
		$this->assertOk($response);

		Queue::assertPushed(RecomputePhotoBucketsJob::class, fn (RecomputePhotoBucketsJob $job) => $job->photo_id === $this->photo1->id);
	}

	public function testUpdateCreatedAtChangeDispatches(): void
	{
		// Pre-sync title_base once so this test isolates `created_at` -
		// mirrors the pre-sync in testUpdateUnrelatedAttributeChangeDoesNotDispatch.
		$this->photo1->title_base = \App\Services\TitleSplitter::split($this->photo1->title)->base;
		$this->photo1->save();
		$this->photo1->refresh();

		Queue::fake([RecomputePhotoBucketsJob::class]);

		$response = $this->actingAs($this->userMayUpload1)->patchJson('Photo', [
			'photo_id' => $this->photo1->id,
			'title' => $this->photo1->title,
			'description' => $this->photo1->description ?? '',
			'tags' => [],
			'license' => 'none',
			'taken_at' => null,
			'upload_date' => $this->photo1->created_at->addDay()->toDateTimeString(),
			'from_id' => $this->album1->id,
		]);
		$this->assertOk($response);

		Queue::assertPushed(RecomputePhotoBucketsJob::class, fn (RecomputePhotoBucketsJob $job) => $job->photo_id === $this->photo1->id);
	}

	public function testUpdateUnrelatedAttributeChangeDoesNotDispatch(): void
	{
		// Pre-sync title_base/taken_at once so the assertion below is not a
		// false positive from title_base's first-ever sync (the factory
		// never sets it) or from taken_at being re-set to Carbon::parse()'s
		// microsecond-quantized version of an already-identical timestamp.
		$this->photo1->title_base = \App\Services\TitleSplitter::split($this->photo1->title)->base;
		$this->photo1->save();
		$this->photo1->refresh();

		Queue::fake([RecomputePhotoBucketsJob::class]);

		$response = $this->actingAs($this->userMayUpload1)->patchJson('Photo', [
			'photo_id' => $this->photo1->id,
			'title' => $this->photo1->title,
			'description' => 'a brand new description',
			'tags' => [],
			'license' => 'none',
			'taken_at' => null,
			// Full microsecond precision, not just the date - `created_at`
			// is itself bucket-relevant (writable here via `uploadDate()`),
			// and the factory stores it with microseconds, so anything
			// coarser (even a full "Y-m-d H:i:s" string) truncates them and
			// registers as a real change, defeating the point of this
			// "unrelated attribute" test.
			'upload_date' => $this->photo1->created_at->format('Y-m-d H:i:s.u'),
			'from_id' => $this->album1->id,
		]);
		$this->assertOk($response);

		Queue::assertNotPushed(RecomputePhotoBucketsJob::class);
	}

	// ── PhotoController::rename() ──────────────────────────────────

	public function testRenameDispatches(): void
	{
		Queue::fake([RecomputePhotoBucketsJob::class]);

		$response = $this->actingAs($this->userMayUpload1)->patchJson('Photo::rename', [
			'photo_id' => $this->photo1->id,
			'title' => 'renamed_title_7',
		]);
		$this->assertNoContent($response);

		Queue::assertPushed(RecomputePhotoBucketsJob::class, fn (RecomputePhotoBucketsJob $job) => $job->photo_id === $this->photo1->id);
	}

	// ── PhotoController::highlight() ────────────────────────────────

	public function testHighlightChangeDispatches(): void
	{
		Queue::fake([RecomputePhotoBucketsJob::class]);

		$response = $this->actingAs($this->userMayUpload1)->postJson('Photo::highlight', [
			'photo_ids' => [$this->photo1->id],
			'is_highlighted' => true,
		]);
		$this->assertNoContent($response);

		Queue::assertPushed(RecomputePhotoBucketsJob::class, fn (RecomputePhotoBucketsJob $job) => $job->photo_id === $this->photo1->id);
	}

	public function testHighlightUnchangedValueDoesNotDispatch(): void
	{
		// $this->photo1 defaults to is_highlighted = false.
		Queue::fake([RecomputePhotoBucketsJob::class]);

		$response = $this->actingAs($this->userMayUpload1)->postJson('Photo::highlight', [
			'photo_ids' => [$this->photo1->id],
			'is_highlighted' => false,
		]);
		$this->assertNoContent($response);

		Queue::assertNotPushed(RecomputePhotoBucketsJob::class);
	}

	// ── PhotoController::rate() / Rating::do() ──────────────────────

	public function testRatingChangeDispatches(): void
	{
		Queue::fake([RecomputePhotoBucketsJob::class]);

		$response = $this->actingAs($this->userMayUpload1)->postJson('Photo::setRating', [
			'photo_id' => $this->photo1->id,
			'rating' => 4,
		]);
		$this->assertCreated($response);

		Queue::assertPushed(RecomputePhotoBucketsJob::class, fn (RecomputePhotoBucketsJob $job) => $job->photo_id === $this->photo1->id);
	}

	// ── AlbumController::updateAlbum() ──────────────────────────────

	/** @return array<string,mixed> */
	private function baseAlbumPayload(): array
	{
		return [
			'album_id' => $this->album1->id,
			'title' => 'unchanged title',
			'license' => 'none',
			'description' => '',
			'tags' => [],
			'photo_sorting_column' => null,
			'photo_sorting_order' => null,
			'album_sorting_column' => null,
			'album_sorting_order' => null,
			'album_aspect_ratio' => null,
			'photo_layout' => null,
			'copyright' => '',
			'is_compact' => false,
			'is_pinned' => false,
			'header_id' => null,
			'cover_id' => null,
			'album_timeline' => null,
			'photo_timeline' => null,
		];
	}

	public function testAlbumPhotoSortingColumnChangeDispatchesAlbumPhotoBucketsJob(): void
	{
		Queue::fake([RecomputeAlbumPhotoBucketsJob::class]);

		$payload = $this->baseAlbumPayload();
		$payload['photo_sorting_column'] = 'title';
		$payload['photo_sorting_order'] = 'DESC';

		$response = $this->actingAs($this->userMayUpload1)->patchJson('Album', $payload);
		$this->assertOk($response);

		Queue::assertPushed(RecomputeAlbumPhotoBucketsJob::class, fn (RecomputeAlbumPhotoBucketsJob $job) => $job->album_id === $this->album1->id);
	}

	public function testAlbumUnrelatedAttributeChangeDoesNotDispatchAlbumPhotoBucketsJob(): void
	{
		Queue::fake([RecomputeAlbumPhotoBucketsJob::class]);

		$payload = $this->baseAlbumPayload();
		$payload['title'] = 'a brand new title';

		$response = $this->actingAs($this->userMayUpload1)->patchJson('Album', $payload);
		$this->assertOk($response);

		Queue::assertNotPushed(RecomputeAlbumPhotoBucketsJob::class);
	}
}
