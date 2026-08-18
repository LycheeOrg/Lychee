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

use App\Models\Track;
use Illuminate\Testing\TestResponse;
use Tests\Constants\TestConstants;
use Tests\Feature_v2\Base\BaseApiWithDataTest;

/**
 * v8 multi-track REST surface (`POST`/`PATCH`/`DELETE Album::tracks`).
 *
 * Scenarios covered:
 * - S-055-01: Batch upload creates N rows.
 * - S-055-04: v8 delete of the primary re-resolves `primaryTrack()`.
 * - S-055-05: Rename a non-primary track.
 * - S-055-06: Batch upload all-or-nothing on one invalid file.
 * - S-055-07: Cross-album track_id rejected.
 */
class AlbumTracksControllerTest extends BaseApiWithDataTest
{
	private function uploadTracks(string $album_id, array $samples): TestResponse
	{
		$files = [];
		foreach ($samples as $sample) {
			$files[] = static::createUploadedFile($sample);
		}

		return $this->post(self::API_PREFIX . 'Album::tracks', [
			'album_id' => $album_id,
			'files' => $files,
		], [
			'CONTENT_TYPE' => 'multipart/form-data',
			'Accept' => 'application/json',
		]);
	}

	public function testBatchUploadCreatesOneRowPerFileAndMarksFirstPrimary(): void
	{
		$this->actingAs($this->userMayUpload1);

		$response = $this->uploadTracks($this->album1->id, [TestConstants::SAMPLE_FILE_GPX, TestConstants::SAMPLE_FILE_GPX2]);
		$this->assertOk($response);

		$tracks = Track::query()->where('album_id', '=', $this->album1->id)->orderBy('id')->get();
		self::assertCount(2, $tracks);
		self::assertTrue($tracks->first()->is_primary);
		self::assertFalse($tracks->last()->is_primary);
		self::assertSame('sample', $tracks->first()->name);
		self::assertSame('sample2', $tracks->last()->name);

		$json = $response->json();
		self::assertCount(2, $json);
	}

	public function testBatchUploadDoesNotMarkAnyPrimaryWhenAlbumAlreadyHasTracks(): void
	{
		$this->actingAs($this->userMayUpload1);
		Track::factory()->for_album($this->album1)->primary()->create();

		$this->uploadTracks($this->album1->id, [TestConstants::SAMPLE_FILE_GPX2]);

		$tracks = Track::query()->where('album_id', '=', $this->album1->id)->get();
		self::assertCount(2, $tracks);
		self::assertSame(1, $tracks->where('is_primary', true)->count());
	}

	public function testBatchUploadAllOrNothingOnOneInvalidFile(): void
	{
		$this->actingAs($this->userMayUpload1);

		$response = $this->post(self::API_PREFIX . 'Album::tracks', [
			'album_id' => $this->album1->id,
			'files' => [static::createUploadedFile(TestConstants::SAMPLE_FILE_GPX), ''],
		], [
			'CONTENT_TYPE' => 'multipart/form-data',
			'Accept' => 'application/json',
		]);

		$this->assertUnprocessable($response);
		self::assertSame(0, Track::query()->where('album_id', '=', $this->album1->id)->count());
	}

	public function testRenameUpdatesNameOnlyForNonPrimaryTrack(): void
	{
		$this->actingAs($this->userMayUpload1);
		Track::factory()->for_album($this->album1)->primary()->create();
		$track = Track::factory()->for_album($this->album1)->create(['name' => 'old-name']);

		$response = $this->patchJson('Album::tracks', [
			'album_id' => $this->album1->id,
			'track_id' => $track->id,
			'name' => 'new-name',
		]);
		$this->assertNoContent($response);

		$track->refresh();
		self::assertSame('new-name', $track->name);
		self::assertFalse($track->is_primary);
	}

	public function testDeletePromotesNextOldestWhenPrimaryIsDeleted(): void
	{
		$this->actingAs($this->userMayUpload1);
		$primary = Track::factory()->for_album($this->album1)->primary()->create();
		$second = Track::factory()->for_album($this->album1)->create();

		$response = $this->deleteJson('Album::tracks', [
			'album_id' => $this->album1->id,
			'track_id' => $primary->id,
		]);
		$this->assertNoContent($response);

		self::assertNull(Track::query()->find($primary->id));
		$second->refresh();
		self::assertTrue($second->is_primary);

		self::assertSame($second->id, $this->album1->fresh()->primaryTrack()->first()?->id);
	}

	public function testDeleteNonPrimaryTrackDoesNotDisturbPrimary(): void
	{
		$this->actingAs($this->userMayUpload1);
		$primary = Track::factory()->for_album($this->album1)->primary()->create();
		$other = Track::factory()->for_album($this->album1)->create();

		$response = $this->deleteJson('Album::tracks', [
			'album_id' => $this->album1->id,
			'track_id' => $other->id,
		]);
		$this->assertNoContent($response);

		self::assertNull(Track::query()->find($other->id));
		$primary->refresh();
		self::assertTrue($primary->is_primary);
	}

	public function testRenameRejectsTrackFromDifferentAlbum(): void
	{
		$this->actingAs($this->userMayUpload1);
		$foreignTrack = Track::factory()->for_album($this->album2)->create();

		$response = $this->patchJson('Album::tracks', [
			'album_id' => $this->album1->id,
			'track_id' => $foreignTrack->id,
			'name' => 'hijack',
		]);
		$this->assertNotFound($response);
	}

	public function testDeleteRejectsTrackFromDifferentAlbum(): void
	{
		$this->actingAs($this->userMayUpload1);
		$foreignTrack = Track::factory()->for_album($this->album2)->create();

		$response = $this->deleteJson('Album::tracks', [
			'album_id' => $this->album1->id,
			'track_id' => $foreignTrack->id,
		]);
		$this->assertNotFound($response);
		self::assertNotNull(Track::query()->find($foreignTrack->id));
	}
}
