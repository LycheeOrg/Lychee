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

namespace Tests\Feature_v2\Album;

use App\Events\AlbumChildrenChanged;
use App\Jobs\RecomputeChildAlbumBucketsJob;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Queue;
use Tests\Feature_v2\Base\BaseApiWithDataTest;
use Tests\Traits\RequireSE;

/**
 * Covers Feature 061 FR-061-03 / S-061-14/15: the write site that must
 * dispatch {@see RecomputeChildAlbumBucketsJob} when a parent's own
 * `album_sorting_col`/`album_sorting_order`/`album_timeline` changes, and
 * must NOT dispatch it for an unrelated attribute change.
 */
class AlbumSortingBucketDispatchTest extends BaseApiWithDataTest
{
	use RequireSE;

	/** @return array<string,mixed> */
	private function basePayload(): array
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
			'album_timeline' => null,
			'photo_timeline' => null,
		];
	}

	public function testUnrelatedAttributeChangeDoesNotDispatch(): void
	{
		Queue::fake([RecomputeChildAlbumBucketsJob::class]);

		$payload = $this->basePayload();
		$payload['title'] = 'a brand new title';

		$response = $this->actingAs($this->userMayUpload1)->patchJson('Album', $payload);
		$this->assertOk($response);

		Queue::assertNotPushed(RecomputeChildAlbumBucketsJob::class);
	}

	public function testAlbumSortingColumnChangeDispatches(): void
	{
		Queue::fake([RecomputeChildAlbumBucketsJob::class]);

		$payload = $this->basePayload();
		$payload['album_sorting_column'] = 'title';
		$payload['album_sorting_order'] = 'DESC';

		$response = $this->actingAs($this->userMayUpload1)->patchJson('Album', $payload);
		$this->assertOk($response);

		Queue::assertPushed(RecomputeChildAlbumBucketsJob::class, fn (RecomputeChildAlbumBucketsJob $job) => $job->parent_album_id === $this->album1->id);
	}

	public function testAlbumTimelineChangeDispatches(): void
	{
		$this->requireSe();
		Queue::fake([RecomputeChildAlbumBucketsJob::class]);

		$payload = $this->basePayload();
		$payload['album_timeline'] = 'month';

		$response = $this->actingAs($this->userMayUpload1)->patchJson('Album', $payload);
		$this->assertOk($response);

		Queue::assertPushed(RecomputeChildAlbumBucketsJob::class, fn (RecomputeChildAlbumBucketsJob $job) => $job->parent_album_id === $this->album1->id);
	}

	/**
	 * {@see RecomputeChildAlbumBucketsJob} bulk-`upsert()`s its writes,
	 * bypassing Eloquent events entirely - nothing else in this write path
	 * invalidates `$album`'s own children-listing cache (the one the bucket
	 * recompute affects) unless {@see AlbumChildrenChanged} is dispatched
	 * for it explicitly.
	 */
	public function testAlbumSortingColumnChangeDispatchesChildrenChangedForCacheInvalidation(): void
	{
		Event::fake([AlbumChildrenChanged::class]);

		$payload = $this->basePayload();
		$payload['album_sorting_column'] = 'title';
		$payload['album_sorting_order'] = 'DESC';

		$response = $this->actingAs($this->userMayUpload1)->patchJson('Album', $payload);
		$this->assertOk($response);

		Event::assertDispatched(AlbumChildrenChanged::class, fn (AlbumChildrenChanged $event) => $event->parent_ids === [$this->album1->id]);
	}

	public function testUnrelatedAttributeChangeDoesNotDispatchChildrenChanged(): void
	{
		Event::fake([AlbumChildrenChanged::class]);

		$payload = $this->basePayload();
		$payload['title'] = 'a brand new title';

		$response = $this->actingAs($this->userMayUpload1)->patchJson('Album', $payload);
		$this->assertOk($response);

		Event::assertNotDispatched(AlbumChildrenChanged::class);
	}
}
