<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

/**
 * @noinspection PhpDocMissingThrowsInspection
 * @noinspection PhpUnhandledExceptionInspection
 */

namespace Tests\Feature_v2;

use App\Enum\SmartAlbumType;
use App\Events\PhotoWillBeDeleted;
use App\Jobs\RecomputeAlbumUserThumbsJob;
use App\Jobs\WebhookDispatchJob;
use App\Listeners\WebhookListener;
use App\Models\Photo;
use App\Models\Webhook;
use Illuminate\Queue\Events\JobProcessing;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Queue;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Feature_v2\Base\BaseApiWithDataTest;

/**
 * FR-112-05..07, FR-112-12: a multi-photo delete no longer fans out per photo.
 *
 * Scenarios covered:
 * - S-112-05/06: `PhotoWillBeDeleted` only with an enabled `photo.delete` webhook.
 * - S-112-07: each thumb job (kind, album) runs once, after the photos are gone.
 * - S-112-08: soft delete only, no thumb job.
 * - S-112-09: `PhotoWillBeDeleted` reaches `WebhookListener` only.
 * - S-112-18 (thumb part): thumb jobs after response on `sync`, queued otherwise.
 * - S-112-21: delete webhooks after response on `sync`, queued otherwise.
 */
class PhotoDeleteFanOutTest extends BaseApiWithDataTest
{
	public function testNoPhotoWillBeDeletedWithoutDeleteWebhook(): void
	{
		Config::set('features.webhook', true);
		Webhook::factory()->onPhotoAdd()->create();
		Webhook::factory()->onPhotoDelete()->disabled()->create();
		Event::fake([PhotoWillBeDeleted::class]);

		$this->deleteBothAlbum1Photos();

		Event::assertNotDispatched(PhotoWillBeDeleted::class);
	}

	public function testOnePhotoWillBeDeletedPerPhotoWithDeleteWebhook(): void
	{
		Config::set('features.webhook', true);
		Webhook::factory()->onPhotoDelete()->create();
		Event::fake([PhotoWillBeDeleted::class]);

		$this->deleteBothAlbum1Photos();

		Event::assertDispatchedTimes(PhotoWillBeDeleted::class, 2);
		Event::assertDispatched(PhotoWillBeDeleted::class, fn (PhotoWillBeDeleted $e) => $e->photo_id === $this->photo1->id && $e->album_id === $this->album1->id);
		Event::assertDispatched(PhotoWillBeDeleted::class, fn (PhotoWillBeDeleted $e) => $e->photo_id === $this->photo1b->id && $e->album_id === $this->album1->id);
	}

	/**
	 * @return array<string,array{0:bool}>
	 */
	public static function webhookFeatureProvider(): array
	{
		return [
			'webhook feature on' => [true],
			'webhook feature off' => [false],
		];
	}

	#[DataProvider('webhookFeatureProvider')]
	public function testEachThumbJobRunsOnceAfterThePhotosAreGone(bool $webhook_feature): void
	{
		Config::set('features.webhook', $webhook_feature);
		$deleted_ids = [$this->photo1->id, $this->photo1b->id];

		/** @var array<int,array{0:string,1:string,2:int}> $runs (kind, album_id, photos left) */
		$runs = [];
		Queue::before(function (JobProcessing $event) use (&$runs, $deleted_ids): void {
			$job = unserialize($event->job->payload()['data']['command']);
			if ($job instanceof RecomputeAlbumUserThumbsJob) {
				// Feature 119: "gone" means hidden (trashed) for every photo query.
				$runs[] = [$job->album_kind, $job->album_id, Photo::query()->whereIn('id', $deleted_ids)->count()];
			}
		});

		$this->deleteBothAlbum1Photos();

		$keys = array_map(fn (array $run) => $run[0] . '|' . $run[1], $runs);
		self::assertSame(array_values(array_unique($keys)), $keys, 'A thumb job ran more than once.');
		foreach (SmartAlbumType::cases() as $smart_album_type) {
			self::assertContains(RecomputeAlbumUserThumbsJob::KIND_SMART . '|' . $smart_album_type->value, $keys);
		}
		self::assertContains(RecomputeAlbumUserThumbsJob::KIND_TAG . '|' . $this->tagAlbum1->id, $keys);
		foreach ($runs as $run) {
			self::assertSame(0, $run[2], 'A thumb job ran before the photos were deleted.');
		}
	}

	public function testSoftDeleteOnlyDispatchesNoThumbJob(): void
	{
		Config::set('features.webhook', true);
		Bus::fake();
		$this->photo1b->albums()->attach($this->album5->id);

		$response = $this->actingAs($this->userMayUpload1)->deleteJson('Photo', [
			'photo_ids' => [$this->photo1b->id],
			'from_id' => $this->album1->id,
		]);
		$this->assertNoContent($response);

		$this->assertDatabaseHas('photos', ['id' => $this->photo1b->id]);
		Bus::assertNotDispatched(RecomputeAlbumUserThumbsJob::class);
	}

	public function testPhotoWillBeDeletedReachesOnlyTheWebhookListener(): void
	{
		self::assertSame(
			[WebhookListener::class . '@handlePhotoWillBeDeleted'],
			Event::getRawListeners()[PhotoWillBeDeleted::class] ?? []
		);
	}

	public function testThumbJobsAreDispatchedAfterResponseOnSync(): void
	{
		// The thumb refresh does not depend on the webhook feature (FR-112-07).
		Config::set('features.webhook', false);
		Bus::fake();

		$this->deleteBothAlbum1Photos();

		Bus::assertDispatchedAfterResponse(RecomputeAlbumUserThumbsJob::class, fn (RecomputeAlbumUserThumbsJob $job) => $job->album_id === $this->tagAlbum1->id);
		self::assertCount(0, Bus::dispatched(RecomputeAlbumUserThumbsJob::class));
	}

	public function testThumbJobsAreQueuedOnAsyncQueue(): void
	{
		config(['queue.default' => 'database']);
		Config::set('features.webhook', false);
		Bus::fake();

		$this->deleteBothAlbum1Photos();

		Bus::assertDispatched(RecomputeAlbumUserThumbsJob::class, fn (RecomputeAlbumUserThumbsJob $job) => $job->album_id === $this->tagAlbum1->id);
		Bus::assertNotDispatchedAfterResponse(RecomputeAlbumUserThumbsJob::class);
	}

	public function testDeleteWebhooksAreDispatchedAfterResponseOnSync(): void
	{
		Config::set('features.webhook', true);
		Webhook::factory()->onPhotoDelete()->create();
		Webhook::factory()->onPhotoDelete()->disabled()->create();
		Bus::fake();

		$this->deleteBothAlbum1Photos();

		Bus::assertDispatchedAfterResponseTimes(WebhookDispatchJob::class, 2);
		self::assertCount(0, Bus::dispatched(WebhookDispatchJob::class));
		foreach ([$this->photo1, $this->photo1b] as $photo) {
			Bus::assertDispatchedAfterResponse(
				WebhookDispatchJob::class,
				fn (WebhookDispatchJob $job) => $job->payload->photo_id === $photo->id &&
					$job->payload->album_id === $this->album1->id &&
					$job->payload->title === $photo->title
			);
		}
	}

	public function testDeleteWebhooksAreQueuedOnAsyncQueue(): void
	{
		config(['queue.default' => 'database']);
		Config::set('features.webhook', true);
		Webhook::factory()->onPhotoDelete()->create();
		Bus::fake();

		$this->deleteBothAlbum1Photos();

		self::assertCount(2, Bus::dispatched(WebhookDispatchJob::class));
		Bus::assertNotDispatchedAfterResponse(WebhookDispatchJob::class);
	}

	private function deleteBothAlbum1Photos(): void
	{
		$response = $this->actingAs($this->userMayUpload1)->deleteJson('Photo', [
			'photo_ids' => [$this->photo1->id, $this->photo1b->id],
			'from_id' => $this->album1->id,
		]);
		$this->assertNoContent($response);
		// Feature 119: the photos are trashed (hidden), the purge deletes their rows.
		self::assertFalse(Photo::query()->whereKey($this->photo1->id)->exists());
		self::assertFalse(Photo::query()->whereKey($this->photo1b->id)->exists());
	}
}
