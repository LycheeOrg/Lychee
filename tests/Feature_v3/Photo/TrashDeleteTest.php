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

namespace Tests\Feature_v3\Photo;

use App\Actions\Photo\PurgeTrash;
use App\Constants\QueryLimits;
use App\Enum\SmartAlbumType;
use App\Jobs\DeleteFaceEmbeddingsJob;
use App\Jobs\FileDeleterJob;
use App\Jobs\PurgeTrashJob;
use App\Jobs\RecomputeAlbumUserThumbsJob;
use App\Jobs\RecomputePersonStatsJob;
use App\Jobs\WebhookDispatchJob;
use App\Models\Album;
use App\Models\Webhook;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Tests\Feature_v3\Base\BaseApiWithDataTest;
use Tests\Traits\CreatesBulkFixtures;

/**
 * Feature 119: deletes put photos into the trash, the purge deletes them.
 *
 * The delete routes are v2 (`DELETE /api/v2/Album`, `DELETE /api/v2/Photo`),
 * reached through the inherited v2 helpers.
 */
class TrashDeleteTest extends BaseApiWithDataTest
{
	use CreatesBulkFixtures;

	/**
	 * S-119-01, NFR-119-01/02: an album delete over two chunks of photos
	 * trashes them without touching `size_variants` or writing `photos`.
	 */
	public function testAlbumDeleteTrashesPhotosWithoutTouchingTheirRows(): void
	{
		Config::set('features.webhook', false);
		Bus::fake();
		$album = Album::factory()->as_root()->owned_by($this->userMayUpload1)->create();
		$photo_ids = $this->bulkPhotos($this->userMayUpload1, QueryLimits::ID_CHUNK_SIZE + 2, [$album->id]);
		$this->listenToBindings();
		/** @var string[] $statements */
		$statements = [];
		DB::listen(function (QueryExecuted $query) use (&$statements): void {
			$statements[] = strtolower($query->sql);
		});

		$response = $this->actingAs($this->userMayUpload1)->deleteJson('Album', ['album_ids' => [$album->id]]);

		$this->assertNoContent($response);
		$this->assertDatabaseMissing('albums', ['id' => $album->id]);
		self::assertSame(count($photo_ids), $this->countTrashed($photo_ids));
		self::assertSame([], array_values(array_filter($statements, fn (string $sql) => str_contains($sql, '"size_variants"'))), 'A statement touches size_variants.');
		self::assertSame([], array_values(array_filter($statements, fn (string $sql) => preg_match('/^(update|delete from|insert into) "photos"/', $sql) === 1)), 'A statement writes photos.');
		$this->assertBindingsWithinLimit();
		Bus::assertDispatchedAfterResponse(PurgeTrashJob::class);
	}

	public function testAlbumDeleteQueuesThePurgeWithAWorker(): void
	{
		Config::set('queue.default', 'database');
		Bus::fake();
		$album = Album::factory()->as_root()->owned_by($this->userMayUpload1)->create();
		$this->bulkPhotos($this->userMayUpload1, 2, [$album->id]);

		$response = $this->actingAs($this->userMayUpload1)->deleteJson('Album', ['album_ids' => [$album->id]]);

		$this->assertNoContent($response);
		Bus::assertDispatched(PurgeTrashJob::class);
		Bus::assertNotDispatchedAfterResponse(PurgeTrashJob::class);
	}

	/**
	 * S-119-12: webhooks, cover reset and thumb jobs come with the delete;
	 * file, person-stats and face-embedding jobs only from the purge.
	 */
	public function testSideEffectsVisibleToUsersComeWithTheDelete(): void
	{
		Config::set('features.webhook', true);
		Webhook::factory()->onPhotoDelete()->create();
		Bus::fake();
		DB::table('albums')->where('id', '=', $this->album2->id)->update(['cover_id' => $this->photo1b->id]);

		$response = $this->actingAs($this->userMayUpload1)->deleteJson('Photo', [
			'photo_ids' => [$this->photo1b->id],
			'from_id' => $this->album1->id,
		]);

		$this->assertNoContent($response);
		self::assertSame(1, $this->countTrashed([$this->photo1b->id]));
		self::assertNull(DB::table('albums')->where('id', '=', $this->album2->id)->value('cover_id'));
		Bus::assertDispatchedAfterResponse(WebhookDispatchJob::class);
		Bus::assertDispatchedAfterResponse(RecomputeAlbumUserThumbsJob::class);
		Bus::assertDispatchedAfterResponse(PurgeTrashJob::class);
		Bus::assertNotDispatched(FileDeleterJob::class);
		Bus::assertNotDispatched(RecomputePersonStatsJob::class);
		Bus::assertNotDispatched(DeleteFaceEmbeddingsJob::class);

		(new PurgeTrash())->run();

		Bus::assertDispatched(FileDeleterJob::class);
		$this->assertDatabaseMissing('photos', ['id' => $this->photo1b->id]);
	}

	/**
	 * S-119-17 (Q-119-06 C): smart albums other than Unsorted and tag albums
	 * hold no linked photo, so a delete from them is refused; Unsorted works
	 * for photos without an album.
	 */
	public function testDeleteFromAVirtualAlbumIsRefused(): void
	{
		foreach ([SmartAlbumType::RECENT->value, $this->tagAlbum1->id] as $from_id) {
			$response = $this->actingAs($this->userMayUpload1)->deleteJson('Photo', [
				'photo_ids' => [$this->photo1->id],
				'from_id' => $from_id,
			]);
			$this->assertUnprocessable($response);
		}
		self::assertSame(0, $this->countTrashed([$this->photo1->id]));
		$this->assertDatabaseHas('photo_album', ['photo_id' => $this->photo1->id, 'album_id' => $this->album1->id]);

		$response = $this->actingAs($this->userMayUpload1)->deleteJson('Photo', [
			'photo_ids' => [$this->photoUnsorted->id],
			'from_id' => SmartAlbumType::UNSORTED->value,
		]);
		$this->assertNoContent($response);
		// On `sync` the purge runs when the request ends: the photo is gone.
		$this->assertDatabaseMissing('photos', ['id' => $this->photoUnsorted->id]);
	}

	/**
	 * S-119-18 (Q-119-07 A): a selected photo that is not in `from_id`, or
	 * that is in an album when deleting from Unsorted, refuses the request.
	 */
	public function testDeleteOfAPhotoOutsideTheAlbumIsRefused(): void
	{
		$response = $this->actingAs($this->userMayUpload1)->deleteJson('Photo', [
			'photo_ids' => [$this->photo1->id, $this->subPhoto1->id],
			'from_id' => $this->album1->id,
		]);
		$this->assertUnprocessable($response);
		$response->assertSee($this->subPhoto1->id);
		self::assertSame(0, $this->countTrashed([$this->photo1->id, $this->subPhoto1->id]));
		$this->assertDatabaseHas('photo_album', ['photo_id' => $this->photo1->id, 'album_id' => $this->album1->id]);

		$response = $this->actingAs($this->userMayUpload1)->deleteJson('Photo', [
			'photo_ids' => [$this->photo1->id],
			'from_id' => SmartAlbumType::UNSORTED->value,
		]);
		$this->assertUnprocessable($response);
		self::assertSame(0, $this->countTrashed([$this->photo1->id]));
	}

	/**
	 * @param string[] $photo_ids
	 */
	private function countTrashed(array $photo_ids): int
	{
		$count = 0;
		foreach (array_chunk($photo_ids, QueryLimits::ID_CHUNK_SIZE) as $chunk) {
			$count += DB::table('trashed_photos')->whereIn('photo_id', $chunk)->count();
		}

		return $count;
	}
}
