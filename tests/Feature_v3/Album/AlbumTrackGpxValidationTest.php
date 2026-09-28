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

namespace Tests\Feature_v3\Album;

use App\Models\Track;
use Illuminate\Http\UploadedFile;
use Tests\Constants\TestConstants;
use Tests\Feature_v3\Base\BaseApiWithDataTest;

/**
 * Track uploads (`Album::track`, `Album::tracks`) reject files which are not genuine GPX.
 */
class AlbumTrackGpxValidationTest extends BaseApiWithDataTest
{
	private const HEADERS = [
		'CONTENT_TYPE' => 'multipart/form-data',
		'Accept' => 'application/json',
	];

	private static function maliciousGpx(): UploadedFile
	{
		$tmp = tempnam(sys_get_temp_dir(), 'lychee');
		file_put_contents($tmp, '<?xml version="1.0"?><!DOCTYPE gpx [<!ENTITY xxe SYSTEM "file:///etc/passwd">]>'
			. '<gpx version="1.1" creator="x" xmlns="http://www.topografix.com/GPX/1/1"><name>&xxe;</name></gpx>');

		return new UploadedFile($tmp, 'evil.gpx', TestConstants::MIME_TYPE_APP_GPX, UPLOAD_ERR_OK, true);
	}

	public function testSingleUploadRejectsInvalidGpx(): void
	{
		$response = $this->actingAs($this->userMayUpload1)->post(self::API_PREFIX . 'Album::track', [
			'album_id' => $this->album1->id,
			'file' => self::maliciousGpx(),
		], self::HEADERS);

		$this->assertUnprocessable($response);
		$response->assertJsonValidationErrors('file');
		self::assertSame(0, Track::query()->where('album_id', '=', $this->album1->id)->count());
	}

	public function testBatchUploadRejectsInvalidGpx(): void
	{
		$response = $this->actingAs($this->userMayUpload1)->post(self::API_PREFIX . 'Album::tracks', [
			'album_id' => $this->album1->id,
			'files' => [static::createUploadedFile(TestConstants::SAMPLE_FILE_GPX), self::maliciousGpx()],
		], self::HEADERS);

		$this->assertUnprocessable($response);
		$response->assertJsonValidationErrors('files.1');
		self::assertSame(0, Track::query()->where('album_id', '=', $this->album1->id)->count());
	}

	public function testUploadAcceptsValidGpx(): void
	{
		$response = $this->actingAs($this->userMayUpload1)->post(self::API_PREFIX . 'Album::tracks', [
			'album_id' => $this->album1->id,
			'files' => [static::createUploadedFile(TestConstants::SAMPLE_FILE_GPX), static::createUploadedFile(TestConstants::SAMPLE_FILE_GPX2)],
		], self::HEADERS);

		$this->assertOk($response);
		self::assertSame(2, Track::query()->where('album_id', '=', $this->album1->id)->count());
	}
}
