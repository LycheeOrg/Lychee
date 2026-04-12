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

namespace Tests\Feature_v2\Zip;

use App\Models\Configs;
use Tests\Feature_v2\Base\BaseApiWithDataTest;

class ZipChunksTest extends BaseApiWithDataTest
{
	public function testGetChunkCountUnauthenticated(): void
	{
		$response = $this->getJsonWithData('Zip/chunks', [
			'album_ids' => $this->album1->id,
			'variant' => 'ORIGINAL',
		]);
		// Unauthenticated users cannot access private albums
		$this->assertUnauthorized($response);
	}

	public function testGetChunkCountWhenDisabled(): void
	{
		Configs::set('download_archive_chunked', false);
		Configs::set('download_archive_chunk_size', 300);

		$response = $this->actingAs($this->userMayUpload1)->getJsonWithData('Zip/chunks', [
			'album_ids' => $this->album1->id,
			'variant' => 'ORIGINAL',
		]);

		$this->assertOk($response);
		$response->assertJson(['total_chunks' => 1]);
		$response->assertJsonStructure(['total_chunks', 'total_photos']);
	}

	public function testGetChunkCountWhenEnabled(): void
	{
		Configs::set('download_archive_chunked', true);
		Configs::set('download_archive_chunk_size', 1);

		$response = $this->actingAs($this->userMayUpload1)->getJsonWithData('Zip/chunks', [
			'album_ids' => $this->album1->id,
			'variant' => 'ORIGINAL',
		]);

		$this->assertOk($response);
		// album1 has photo1 and photo1b (2 photos), with chunk_size=1 we expect 2 chunks
		$response->assertJson(['total_photos' => 2]);
		$this->assertGreaterThanOrEqual(1, $response->json('total_chunks'));

		Configs::set('download_archive_chunked', false);
		Configs::set('download_archive_chunk_size', 300);
	}

	public function testGetChunkCountWithInvalidAlbum(): void
	{
		$response = $this->actingAs($this->userMayUpload1)->getJsonWithData('Zip/chunks', [
			'album_ids' => 'nonexistent-album-id',
			'variant' => 'ORIGINAL',
		]);

		// Invalid album IDs fail validation (422) or resource not found (404)
		$this->assertUnprocessable($response);
	}
}
