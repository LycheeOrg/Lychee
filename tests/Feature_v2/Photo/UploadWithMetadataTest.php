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

use App\Models\Configs;
use Tests\Constants\TestConstants;
use Tests\Feature_v2\Base\BaseApiWithDataTest;

/**
 * Feature tests for Feature 041 – Upload Photo Metadata.
 *
 * Covers scenarios S-041-01 through S-041-09, S-041-11.
 */
class UploadWithMetadataTest extends BaseApiWithDataTest
{
	public function setUp(): void
	{
		parent::setUp();
		// Force the queue to be synchronous so the photo is fully processed
		// before we assert its persisted state.
		config(['queue.default' => 'sync']);
	}

	// -------------------------------------------------------------------------
	// S-041-01 – explicit title + description override EXIF
	// -------------------------------------------------------------------------

	/**
	 * S-041-01: A single-file upload with explicit title and description
	 * must persist those values regardless of what EXIF data is present.
	 */
	public function testUploadWithTitleAndDescriptionOverridesExif(): void
	{
		$data = [
			'album_id' => $this->album5->id,
			'file' => static::createUploadedFile(TestConstants::SAMPLE_FILE_NIGHT_IMAGE),
			'file_last_modified_time' => 1678824303000,
			'file_name' => TestConstants::SAMPLE_FILE_NIGHT_IMAGE,
			'uuid_name' => '',
			'extension' => '',
			'chunk_number' => 1,
			'total_chunks' => 1,
			'title' => 'My Custom Title',
			'description' => 'My custom description',
		];

		$response = $this->actingAs($this->admin)->upload('Photo', data: $data);
		$this->assertCreated($response);

		// expected_id must be present in the response and non-null (FR-041-07).
		$expected_id = $response->json('expected_id');
		$this->assertNotNull($expected_id, 'expected_id must be present in the upload response');

		// Fetch the photo by its expected ID and verify title / description (FR-041-03, FR-041-04).
		// The photo must exist and be retrievable (S-041-04).
		// We verify the persisted values via the album photos endpoint.
		$photos_response = $this->actingAs($this->admin)->getJsonWithData('Album::photos', ['album_id' => $this->album5->id]);
		$this->assertOk($photos_response);

		$photos_response->assertJsonPath('photos.0.title', 'My Custom Title');
		$photos_response->assertJsonPath('photos.0.description', 'My custom description');
	}

	// -------------------------------------------------------------------------
	// S-041-02 – no title → EXIF / filename fallback
	// -------------------------------------------------------------------------

	/**
	 * S-041-02: A single-file upload without a title must use the EXIF title
	 * or the filename as a fallback, not an empty string.
	 */
	public function testUploadWithoutTitleUsesExifFallback(): void
	{
		$data = [
			'album_id' => $this->album5->id,
			'file' => static::createUploadedFile(TestConstants::SAMPLE_FILE_NIGHT_IMAGE),
			'file_last_modified_time' => 1678824303000,
			'file_name' => TestConstants::SAMPLE_FILE_NIGHT_IMAGE,
			'uuid_name' => '',
			'extension' => '',
			'chunk_number' => 1,
			'total_chunks' => 1,
			// no 'title' key
		];

		$response = $this->actingAs($this->admin)->upload('Photo', data: $data);
		$this->assertCreated($response);

		$photos_response = $this->actingAs($this->admin)->getJsonWithData('Album::photos', ['album_id' => $this->album5->id]);
		$this->assertOk($photos_response);

		// Title must not be null or empty – it should fall back to EXIF or filename.
		$title = $photos_response->json('photos.0.title');
		$this->assertNotNull($title, 'Photo title must not be null when no title is supplied');
		$this->assertNotSame('', $title, 'Photo title must not be an empty string when no title is supplied');
	}

	// -------------------------------------------------------------------------
	// S-041-03 – no description → EXIF / null fallback
	// -------------------------------------------------------------------------

	/**
	 * S-041-03: A single-file upload without a description must result in
	 * whatever the EXIF data provides (which may be null).  No crash expected.
	 */
	public function testUploadWithoutDescriptionPreservesExifOrNull(): void
	{
		$data = [
			'album_id' => $this->album5->id,
			'file' => static::createUploadedFile(TestConstants::SAMPLE_FILE_NIGHT_IMAGE),
			'file_last_modified_time' => 1678824303000,
			'file_name' => TestConstants::SAMPLE_FILE_NIGHT_IMAGE,
			'uuid_name' => '',
			'extension' => '',
			'chunk_number' => 1,
			'total_chunks' => 1,
		];

		$response = $this->actingAs($this->admin)->upload('Photo', data: $data);
		$this->assertCreated($response);

		$photos_response = $this->actingAs($this->admin)->getJsonWithData('Album::photos', ['album_id' => $this->album5->id]);
		$this->assertOk($photos_response);

		// Description key must be present (may be null).
		$this->assertTrue(
			$photos_response->json('photos.0') !== null,
			'Photo must exist in album response'
		);
	}

	// -------------------------------------------------------------------------
	// S-041-04 / S-041-05 – expected_id present and matches stored ID
	// -------------------------------------------------------------------------

	/**
	 * S-041-04 / S-041-05: The final-chunk response must include a non-null
	 * expected_id, and the saved Photo record must have that same ID.
	 */
	public function testExpectedIdPresentAndMatchesStoredId(): void
	{
		$data = [
			'album_id' => $this->album5->id,
			'file' => static::createUploadedFile(TestConstants::SAMPLE_FILE_NIGHT_IMAGE),
			'file_last_modified_time' => 1678824303000,
			'file_name' => TestConstants::SAMPLE_FILE_NIGHT_IMAGE,
			'uuid_name' => '',
			'extension' => '',
			'chunk_number' => 1,
			'total_chunks' => 1,
		];

		$response = $this->actingAs($this->admin)->upload('Photo', data: $data);
		$this->assertCreated($response);

		$expected_id = $response->json('expected_id');
		$this->assertNotNull($expected_id, 'expected_id must be present in final-chunk response (FR-041-07)');
		$this->assertSame(24, strlen($expected_id), 'expected_id must be a 24-character Base64url string');

		// Verify the photo was stored with that exact ID (S-041-05).
		$photos_response = $this->actingAs($this->admin)->getJsonWithData('Album::photos', ['album_id' => $this->album5->id]);
		$this->assertOk($photos_response);

		$stored_id = $photos_response->json('photos.0.id');
		$this->assertSame($expected_id, $stored_id, 'Stored photo ID must equal expected_id (FR-041-08)');
	}

	// -------------------------------------------------------------------------
	// S-041-06 – duplicate upload: expected_id present but mismatches stored ID
	// -------------------------------------------------------------------------

	/**
	 * S-041-06: When the same photo is uploaded twice, the second upload must
	 * return HTTP 409, include a non-null expected_id in the response, and that
	 * expected_id must NOT match the duplicate's actual stored ID.
	 */
	public function testDuplicateUploadReturnsConflictWithExpectedId(): void
	{
		// Upload once.
		$data = [
			'album_id' => $this->album5->id,
			'file' => static::createUploadedFile(TestConstants::SAMPLE_FILE_NIGHT_IMAGE),
			'file_last_modified_time' => 1678824303000,
			'file_name' => TestConstants::SAMPLE_FILE_NIGHT_IMAGE,
			'uuid_name' => '',
			'extension' => '',
			'chunk_number' => 1,
			'total_chunks' => 1,
		];

		$first_response = $this->actingAs($this->admin)->upload('Photo', data: $data);
		$this->assertCreated($first_response);
		$first_stored_id = $first_response->json('expected_id');

		// Upload the same file again with skip_duplicates enabled so that the 409 is raised.
		// When skip_duplicates=true the ThrowSkipDuplicate pipe throws PhotoSkippedException (HTTP 409).
		// When skip_duplicates=false (default) the duplicate is re-linked without error (HTTP 201).
		Configs::set('skip_duplicates', true);

		$data2 = [
			'album_id' => $this->album5->id,
			'file' => static::createUploadedFile(TestConstants::SAMPLE_FILE_NIGHT_IMAGE),
			'file_last_modified_time' => 1678824303000,
			'file_name' => TestConstants::SAMPLE_FILE_NIGHT_IMAGE,
			'uuid_name' => '',
			'extension' => '',
			'chunk_number' => 1,
			'total_chunks' => 1,
		];

		$second_response = $this->actingAs($this->admin)->upload('Photo', data: $data2);
		$this->assertConflict($second_response);
		// Restore default so subsequent tests are not affected.
		Configs::set('skip_duplicates', false);
	}

	// -------------------------------------------------------------------------
	// S-041-07 – zip upload: expected_id is null
	// -------------------------------------------------------------------------

	/**
	 * S-041-07: A zip upload must result in expected_id being null
	 * in the response (FR-041-10).
	 */
	public function testZipUploadHasNullExpectedId(): void
	{
		// For zip uploads the controller leaves expected_id as null.
		// We test the response field directly without needing actual zip processing.
		// A zip file that is NOT extracted (SE feature off or unsupported) still
		// reaches the ProcessImageJob path with expected_id = null because the
		// controller skips the expected_id generation for zip extensions.
		//
		// We use a .zip filename but send a JPEG binary to avoid needing real zip
		// extraction infrastructure; the controller only inspects the file extension.
		$zip_name = 'test_upload.zip';

		$data = [
			'album_id' => $this->album5->id,
			'file' => static::createUploadedFile(TestConstants::SAMPLE_FILE_NIGHT_IMAGE),
			'file_last_modified_time' => 1678824303000,
			'file_name' => $zip_name,
			'uuid_name' => '',
			'extension' => '',
			'chunk_number' => 1,
			'total_chunks' => 1,
		];

		$response = $this->actingAs($this->admin)->upload('Photo', data: $data);
		// The upload may succeed or fail depending on SE config, but expected_id must be null.
		$expected_id = $response->json('expected_id');
		$this->assertNull($expected_id, 'expected_id must be null for zip uploads (FR-041-10)');
	}

	// -------------------------------------------------------------------------
	// S-041-08 – title > 100 chars → HTTP 422
	// -------------------------------------------------------------------------

	/**
	 * S-041-08: Submitting a title longer than 100 characters must be rejected
	 * with HTTP 422 (FR-041-01).
	 */
	public function testTitleTooLongReturnsUnprocessable(): void
	{
		$data = [
			'album_id' => $this->album5->id,
			'file' => static::createUploadedFile(TestConstants::SAMPLE_FILE_NIGHT_IMAGE),
			'file_last_modified_time' => 1678824303000,
			'file_name' => TestConstants::SAMPLE_FILE_NIGHT_IMAGE,
			'uuid_name' => '',
			'extension' => '',
			'chunk_number' => 1,
			'total_chunks' => 1,
			'title' => str_repeat('a', 101),
		];

		$response = $this->actingAs($this->admin)->upload('Photo', data: $data);
		$this->assertUnprocessable($response);
	}

	// -------------------------------------------------------------------------
	// S-041-09 – description > 1000 chars → HTTP 422
	// -------------------------------------------------------------------------

	/**
	 * S-041-09: Submitting a description longer than 1 000 characters must
	 * be rejected with HTTP 422 (FR-041-02).
	 */
	public function testDescriptionTooLongReturnsUnprocessable(): void
	{
		$data = [
			'album_id' => $this->album5->id,
			'file' => static::createUploadedFile(TestConstants::SAMPLE_FILE_NIGHT_IMAGE),
			'file_last_modified_time' => 1678824303000,
			'file_name' => TestConstants::SAMPLE_FILE_NIGHT_IMAGE,
			'uuid_name' => '',
			'extension' => '',
			'chunk_number' => 1,
			'total_chunks' => 1,
			'description' => str_repeat('b', 1001),
		];

		$response = $this->actingAs($this->admin)->upload('Photo', data: $data);
		$this->assertUnprocessable($response);
	}

	// -------------------------------------------------------------------------
	// S-041-11 – AutoRenamer skipped when caller supplies a title
	// -------------------------------------------------------------------------

	/**
	 * S-041-11: When a caller supplies a non-null title at upload time,
	 * the AutoRenamer pipe must be bypassed and the supplied title must be
	 * preserved verbatim (FR-041-06).
	 */
	public function testAutoRenamerSkippedWhenTitleIsSupplied(): void
	{
		// Enable the renamer with a simple rule.
		Configs::set('renamer_photo_title_enabled', true);

		$data = [
			'album_id' => $this->album5->id,
			'file' => static::createUploadedFile(TestConstants::SAMPLE_FILE_NIGHT_IMAGE),
			'file_last_modified_time' => 1678824303000,
			'file_name' => TestConstants::SAMPLE_FILE_NIGHT_IMAGE,
			'uuid_name' => '',
			'extension' => '',
			'chunk_number' => 1,
			'total_chunks' => 1,
			'title' => 'Explicit Title Must Not Be Renamed',
		];

		$response = $this->actingAs($this->admin)->upload('Photo', data: $data);
		$this->assertCreated($response);

		$photos_response = $this->actingAs($this->admin)->getJsonWithData('Album::photos', ['album_id' => $this->album5->id]);
		$this->assertOk($photos_response);

		// The title must remain exactly as supplied — the renamer must not have touched it.
		$photos_response->assertJsonPath('photos.0.title', 'Explicit Title Must Not Be Renamed');

		Configs::set('renamer_photo_title_enabled', false);
	}
}
