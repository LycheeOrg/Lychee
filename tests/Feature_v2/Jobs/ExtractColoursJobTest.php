<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
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

namespace Tests\Feature_v2\Jobs;

use App\Jobs\ExtractColoursJob;
use App\Models\Configs;
use App\Models\Palette;
use App\Models\Photo;
use Tests\Constants\TestConstants;
use Tests\Feature_v2\Base\BaseApiWithDataTest;

class ExtractColoursJobTest extends BaseApiWithDataTest
{
	public function setUp(): void
	{
		parent::setUp();
		$this->catchFailureSilence = [];
	}

	public function testExtractColoursFarzaiImagick(): void
	{
		Configs::set('colour_extraction_driver', 'farzai');
		Configs::set('imagick', true);
		Configs::invalidateCache();
		$this->runExtraction();
	}

	public function testExtractColoursFarzaiGd(): void
	{
		Configs::set('colour_extraction_driver', 'farzai');
		Configs::set('imagick', false);
		Configs::invalidateCache();
		$this->runExtraction();
		Configs::set('imagick', true);
		Configs::invalidateCache();
	}

	private function runExtraction(): void
	{
		$this->catchFailureSilence = [];
		$response = $this->actingAs($this->admin)->upload('Photo', filename: TestConstants::SAMPLE_FILE_NIGHT_IMAGE);
		$this->assertCreated($response);

		$this->clearCachedSmartAlbums();
		$response = $this->getJsonWithData('Album', ['album_id' => 'unsorted']);
		$this->assertOk($response);
		$id1 = $response->json('resource.photos.0.id');

		$photo = Photo::with(['size_variants', 'palette'])->findOrFail($id1);

		$job = new ExtractColoursJob($photo);
		try {
			$job->handle();
		} catch (\Throwable $e) {
			$this->fail('ExtractColoursJob failed with exception: ' . $e->getMessage());
		}

		/** @var Palette $palette */
		$palette = Palette::where('photo_id', $photo->id)->first();
		if ($palette === null) {
			$this->fail('Palette not found for the photo.');
		}
		$palette->delete();
	}

	public function testExtractColoursLeague(): void
	{
		Configs::set('colour_extraction_driver', 'league');
		Configs::invalidateCache();
		$this->runExtraction();
		Configs::set('colour_extraction_driver', 'farzai');
		Configs::invalidateCache();
	}
}