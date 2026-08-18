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
 * Legacy v7 single-track endpoints (`POST`/`DELETE Album::track`), refactored
 * (I2) to delegate to the album's primary {@link Track} row.
 *
 * Scenarios covered:
 * - S-055-02: v7 upload replaces the primary track only, other tracks untouched.
 * - S-055-03: v7 delete removes the primary track only and promotes the next-oldest remaining track.
 */
class AlbumTrackControllerTest extends BaseApiWithDataTest
{
	private function uploadTrack(string $album_id, string $sample = TestConstants::SAMPLE_FILE_GPX): TestResponse
	{
		return $this->post(self::API_PREFIX . 'Album::track', [
			'album_id' => $album_id,
			'file' => static::createUploadedFile($sample),
		], [
			'CONTENT_TYPE' => 'multipart/form-data',
			'Accept' => 'application/json',
		]);
	}

	public function testUploadCreatesPrimaryTrackWhenNoneExists(): void
	{
		$this->actingAs($this->userMayUpload1);

		$response = $this->uploadTrack($this->album1->id);
		$this->assertNoContent($response);

		$tracks = Track::query()->where('album_id', '=', $this->album1->id)->get();
		self::assertCount(1, $tracks);
		self::assertTrue($tracks->first()->is_primary);
		self::assertSame('sample', $tracks->first()->name);
	}

	public function testUploadReplacesPrimaryTrackOnlyLeavingOthersUntouched(): void
	{
		$this->actingAs($this->userMayUpload1);

		// v8-added, non-primary track.
		$other = Track::factory()->for_album($this->album1)->create(['name' => 'other']);
		// Existing primary (as if uploaded via legacy endpoint before).
		$primary = Track::factory()->for_album($this->album1)->primary()->create(['name' => 'old-primary']);

		$response = $this->uploadTrack($this->album1->id, TestConstants::SAMPLE_FILE_GPX2);
		$this->assertNoContent($response);

		$tracks = Track::query()->where('album_id', '=', $this->album1->id)->orderBy('id')->get();
		self::assertCount(2, $tracks);

		$refreshedOther = $tracks->firstWhere('id', $other->id);
		self::assertSame('other', $refreshedOther->name);
		self::assertFalse($refreshedOther->is_primary);

		$refreshedPrimary = $tracks->firstWhere('id', $primary->id);
		self::assertTrue($refreshedPrimary->is_primary);
		self::assertSame('sample2', $refreshedPrimary->name);
	}

	public function testDeleteRemovesOnlyPrimaryTrackAndPromotesNextOldest(): void
	{
		$this->actingAs($this->userMayUpload1);

		$primary = Track::factory()->for_album($this->album1)->primary()->create();
		$second = Track::factory()->for_album($this->album1)->create();

		$response = $this->deleteJson('Album::track', ['album_id' => $this->album1->id]);
		$this->assertNoContent($response);

		self::assertNull(Track::query()->find($primary->id));

		$refreshedSecond = Track::query()->find($second->id);
		self::assertNotNull($refreshedSecond);
		self::assertTrue($refreshedSecond->is_primary);
	}

	public function testDeleteIsNoopWhenNoTrackExists(): void
	{
		$this->actingAs($this->userMayUpload1);

		$response = $this->deleteJson('Album::track', ['album_id' => $this->album1->id]);
		$this->assertNoContent($response);

		self::assertSame(0, Track::query()->where('album_id', '=', $this->album1->id)->count());
	}

	public function testUploadForbiddenWithoutEditRights(): void
	{
		// album2 is owned by userMayUpload2; userMayUpload1 has no grant on it (unlike album1, shared with userMayUpload2).
		$response = $this->actingAs($this->userMayUpload1)->uploadTrack($this->album2->id);
		$this->assertForbidden($response);
	}
}
