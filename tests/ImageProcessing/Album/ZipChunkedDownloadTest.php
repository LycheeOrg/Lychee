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

declare(strict_types=1);

namespace Tests\ImageProcessing\Zip;

use App\Enum\DownloadVariantType;
use App\Models\Configs;
use Tests\Constants\TestConstants;
use Tests\Feature_v2\Base\BaseApiWithDataTest;

class ZipChunkedDownloadTest extends BaseApiWithDataTest
{
	protected function uploadImage(string $filename, string $album_id)
	{
		$this->catchFailureSilence = [];
		$response = $this->actingAs($this->admin)->upload('Photo', filename: $filename, album_id: $album_id);
		$this->assertCreated($response);

		$response = $this->getJsonWithData('Album::photos', ['album_id' => $album_id]);
		$this->assertOk($response);

		return $response;
	}

	public function setUp(): void
	{
		parent::setUp();
		$response = $this->uploadImage(filename: TestConstants::SAMPLE_FILE_NIGHT_IMAGE, album_id: $this->album5->id);
		$response = $this->uploadImage(filename: TestConstants::SAMPLE_FILE_AARHUS, album_id: $this->album5->id);
	}

	/**
	 * S-035-01: No chunk param → single archive (200, application/x-zip).
	 */
	public function testDownloadWithoutChunkParam(): void
	{
		Configs::set('download_archive_chunked', true);
		Configs::set('download_archive_chunk_size', 3);

		$response = $this->actingAs($this->admin)->download(
			album_ids: [$this->album5->id],
			kind: DownloadVariantType::ORIGINAL,
			expectedStatusCode: 200,
		);

		$this->assertOk($response);
		$this->assertEquals('application/x-zip', $response->headers->get('Content-Type'));
		$disposition = $response->headers->get('Content-Disposition');
		$this->assertNotNull($disposition);
		$this->assertStringNotContainsString('.part', $disposition);

		Configs::set('download_archive_chunked', false);
		Configs::set('download_archive_chunk_size', 300);
	}

	/**
	 * S-035-03: chunk=1, album has ≥1 photo, chunk_size=300 → 200, Content-Disposition has no .part (only 1 chunk).
	 * With 2 photos and chunk_size=1 → part1.zip.
	 */
	public function testDownloadChunk1Of2(): void
	{
		Configs::set('download_archive_chunked', true);
		Configs::set('download_archive_chunk_size', 1);

		$response = $this->actingAs($this->admin)->download(
			album_ids: [$this->album5->id],
			kind: DownloadVariantType::ORIGINAL,
			expectedStatusCode: 200,
			extra_params: ['chunk' => 1],
		);

		$this->assertOk($response);
		$this->assertEquals('application/x-zip', $response->headers->get('Content-Type'));
		$disposition = $response->headers->get('Content-Disposition');
		$this->assertNotNull($disposition);
		$this->assertStringContainsString('.part1.zip', $disposition);

		Configs::set('download_archive_chunked', false);
		Configs::set('download_archive_chunk_size', 300);
	}

	/**
	 * S-035-04: chunk=2, 2 photos, chunk_size=1 → 200, .part2.zip.
	 */
	public function testDownloadChunk2Of2(): void
	{
		Configs::set('download_archive_chunked', true);
		Configs::set('download_archive_chunk_size', 1);

		$response = $this->actingAs($this->admin)->download(
			album_ids: [$this->album5->id],
			kind: DownloadVariantType::ORIGINAL,
			expectedStatusCode: 200,
			extra_params: ['chunk' => 2],
		);

		$this->assertOk($response);
		$disposition = $response->headers->get('Content-Disposition');
		$this->assertNotNull($disposition);
		$this->assertStringContainsString('.part2.zip', $disposition);

		Configs::set('download_archive_chunked', false);
		Configs::set('download_archive_chunk_size', 300);
	}

	/**
	 * S-035-05: chunk=0 → 422 (validation: min:1).
	 * Send X-Requested-With: XMLHttpRequest to force JSON validation response on the Zip endpoint.
	 */
	public function testDownloadChunk0IsInvalid(): void
	{
		$response = $this->actingAs($this->userMayUpload1)->getWithParameters(
			self::API_PREFIX . 'Zip',
			[
				'album_ids' => $this->album1->id,
				'variant' => 'ORIGINAL',
				'chunk' => 0,
			],
			[
				'Accept' => '*/*',
				'X-Requested-With' => 'XMLHttpRequest',
			]
		);
		$this->assertUnprocessable($response);
	}

	/**
	 * S-035-06: chunk=99 (> total chunks) → 422.
	 */
	public function testDownloadChunkOutOfRange(): void
	{
		Configs::set('download_archive_chunk_size', 300);

		$this->actingAs($this->userMayUpload1)->download(
			album_ids: [$this->album1->id],
			kind: DownloadVariantType::ORIGINAL,
			expectedStatusCode: 422,
			extra_params: ['chunk' => 99],
		);

		Configs::set('download_archive_chunk_size', 300);
	}

	/**
	 * S-035-07: chunked mode ON, no chunk param → single archive (same as non-chunked).
	 */
	public function testChunkedModeOnWithoutChunkParam(): void
	{
		Configs::set('download_archive_chunked', true);
		Configs::set('download_archive_chunk_size', 300);

		$response = $this->actingAs($this->userMayUpload1)->download(
			album_ids: [$this->album1->id],
			kind: DownloadVariantType::ORIGINAL,
			expectedStatusCode: 200,
		);

		$this->assertOk($response);
		$this->assertEquals('application/x-zip', $response->headers->get('Content-Type'));

		Configs::set('download_archive_chunked', false);
		Configs::set('download_archive_chunk_size', 300);
	}
}
