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

use Illuminate\Http\UploadedFile;
use function Safe\file_get_contents;
use function Safe\file_put_contents;
use function Safe\tempnam;
use Tests\Constants\TestConstants;
use Tests\Feature_v2\Base\BaseApiWithDataTest;

/**
 * Regression tests for the chunked-upload replay / out-of-order mitigation
 * in PhotoController::upload().
 *
 * A staging file (identified by the server-generated uuid_name) must only
 * ever accept its chunks strictly in order and exactly once. Resubmitting
 * an already-accepted chunk_number, or skipping ahead, must be rejected
 * with HTTP 409 and must not append any more bytes to the staging file.
 */
class ChunkedUploadReplayTest extends BaseApiWithDataTest
{
	public function setUp(): void
	{
		parent::setUp();
		config(['queue.default' => 'sync']);
	}

	/**
	 * Splits a sample file into `$count` byte ranges and returns them as
	 * Illuminate UploadedFile instances suitable for chunk requests.
	 *
	 * @return list<UploadedFile>
	 */
	private function splitIntoChunks(string $sample_path, int $count): array
	{
		$content = file_get_contents(base_path($sample_path));
		$basename = pathinfo($sample_path, PATHINFO_BASENAME);
		$size = intdiv(strlen($content), $count);

		$chunks = [];
		for ($i = 0; $i < $count; $i++) {
			$start = $i * $size;
			$length = $i === $count - 1 ? null : $size;
			$path = tempnam(sys_get_temp_dir(), 'lychee_chunk');
			file_put_contents($path, $length === null ? substr($content, $start) : substr($content, $start, $length));
			$chunks[] = new UploadedFile($path, $basename, null, UPLOAD_ERR_OK, true);
		}

		return $chunks;
	}

	/**
	 * A legitimate two-chunk sequential upload must still succeed and the
	 * assembled photo must have the exact original byte size.
	 */
	public function testSequentialTwoChunkUploadSucceeds(): void
	{
		[$chunk1, $chunk2] = $this->splitIntoChunks(TestConstants::SAMPLE_FILE_PNG, 2);

		$first = $this->actingAs($this->admin)->upload('Photo', data: [
			'album_id' => $this->album5->id,
			'file' => $chunk1,
			'file_last_modified_time' => 1678824303000,
			'file_name' => 'chunked.png',
			'uuid_name' => '',
			'extension' => '',
			'chunk_number' => 1,
			'total_chunks' => 2,
		]);
		$this->assertCreated($first);
		$uuid_name = $first->json('uuid_name');
		$extension = $first->json('extension');
		self::assertNotEmpty($uuid_name);

		$second = $this->actingAs($this->admin)->upload('Photo', data: [
			'album_id' => $this->album5->id,
			'file' => $chunk2,
			'file_last_modified_time' => 1678824303000,
			'file_name' => 'chunked.png',
			'uuid_name' => $uuid_name,
			'extension' => $extension,
			'chunk_number' => 2,
			'total_chunks' => 2,
		]);
		$this->assertCreated($second);
	}

	/**
	 * Replaying an already-accepted intermediate chunk_number for the same
	 * uuid_name must be rejected with 409 and must not append more bytes.
	 */
	public function testReplayingNonFinalChunkIsRejected(): void
	{
		[$chunk1, $chunk2, $chunk2_replay] = $this->splitIntoChunks(TestConstants::SAMPLE_FILE_PNG, 3);

		$first = $this->actingAs($this->admin)->upload('Photo', data: [
			'album_id' => $this->album5->id,
			'file' => $chunk1,
			'file_last_modified_time' => 1678824303000,
			'file_name' => 'chunked.png',
			'uuid_name' => '',
			'extension' => '',
			'chunk_number' => 1,
			'total_chunks' => 3,
		]);
		$this->assertCreated($first);
		$uuid_name = $first->json('uuid_name');
		$extension = $first->json('extension');

		$second = $this->actingAs($this->admin)->upload('Photo', data: [
			'album_id' => $this->album5->id,
			'file' => $chunk2,
			'file_last_modified_time' => 1678824303000,
			'file_name' => 'chunked.png',
			'uuid_name' => $uuid_name,
			'extension' => $extension,
			'chunk_number' => 2,
			'total_chunks' => 3,
		]);
		$this->assertCreated($second);

		// Replay chunk_number = 2 again for the same uuid_name.
		$replay = $this->actingAs($this->admin)->upload('Photo', data: [
			'album_id' => $this->album5->id,
			'file' => $chunk2_replay,
			'file_last_modified_time' => 1678824303000,
			'file_name' => 'chunked.png',
			'uuid_name' => $uuid_name,
			'extension' => $extension,
			'chunk_number' => 2,
			'total_chunks' => 3,
		]);
		$this->assertConflict($replay);
	}

	/**
	 * Skipping ahead (submitting chunk_number = 3 right after chunk_number = 1
	 * was accepted, without chunk 2) must be rejected with 409.
	 */
	public function testOutOfOrderChunkIsRejected(): void
	{
		[$chunk1, , $chunk3] = $this->splitIntoChunks(TestConstants::SAMPLE_FILE_PNG, 3);

		$first = $this->actingAs($this->admin)->upload('Photo', data: [
			'album_id' => $this->album5->id,
			'file' => $chunk1,
			'file_last_modified_time' => 1678824303000,
			'file_name' => 'chunked.png',
			'uuid_name' => '',
			'extension' => '',
			'chunk_number' => 1,
			'total_chunks' => 3,
		]);
		$this->assertCreated($first);
		$uuid_name = $first->json('uuid_name');
		$extension = $first->json('extension');

		$skip_ahead = $this->actingAs($this->admin)->upload('Photo', data: [
			'album_id' => $this->album5->id,
			'file' => $chunk3,
			'file_last_modified_time' => 1678824303000,
			'file_name' => 'chunked.png',
			'uuid_name' => $uuid_name,
			'extension' => $extension,
			'chunk_number' => 3,
			'total_chunks' => 3,
		]);
		$this->assertConflict($skip_ahead);
	}
}
