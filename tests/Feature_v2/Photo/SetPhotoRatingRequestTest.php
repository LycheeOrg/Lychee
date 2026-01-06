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

use Tests\Feature_v2\Base\BaseApiWithDataTest;

class SetPhotoRatingRequestTest extends BaseApiWithDataTest
{
	public function testSetRatingWithoutPhotoId(): void
	{
		$response = $this->actingAs($this->userMayUpload1)->postJson('Photo::setRating', [
			'rating' => 5,
		]);
		$this->assertUnprocessable($response);
		$response->assertJsonValidationErrors(['photo_id']);
	}

	public function testSetRatingWithInvalidPhotoId(): void
	{
		$response = $this->actingAs($this->userMayUpload1)->postJson('Photo::setRating', [
			'photo_id' => 'invalid-id',
			'rating' => 5,
		]);
		$this->assertUnprocessable($response);
		$response->assertJsonValidationErrors(['photo_id']);
	}

	public function testSetRatingWithNonExistentPhoto(): void
	{
		// Generate a valid random ID format that doesn't exist in the database
		$nonExistentId = strtr(base64_encode(random_bytes(18)), '+/', '-_');
		$response = $this->actingAs($this->userMayUpload1)->postJson('Photo::setRating', [
			'photo_id' => $nonExistentId,
			'rating' => 5,
		]);
		$this->assertNotFound($response);
	}

	public function testSetRatingWithoutRating(): void
	{
		$response = $this->actingAs($this->userMayUpload1)->postJson('Photo::setRating', [
			'photo_id' => $this->photo1->id,
		]);
		$this->assertUnprocessable($response);
		$response->assertJsonValidationErrors(['rating']);
	}

	public function testSetRatingWithInvalidRatingType(): void
	{
		$response = $this->actingAs($this->userMayUpload1)->postJson('Photo::setRating', [
			'photo_id' => $this->photo1->id,
			'rating' => 'not-a-number',
		]);
		$this->assertUnprocessable($response);
		$response->assertJsonValidationErrors(['rating']);
	}

	public function testSetRatingWithRatingTooLow(): void
	{
		$response = $this->actingAs($this->userMayUpload1)->postJson('Photo::setRating', [
			'photo_id' => $this->photo1->id,
			'rating' => -1,
		]);
		$this->assertUnprocessable($response);
		$response->assertJsonValidationErrors(['rating']);
	}

	public function testSetRatingWithRatingTooHigh(): void
	{
		$response = $this->actingAs($this->userMayUpload1)->postJson('Photo::setRating', [
			'photo_id' => $this->photo1->id,
			'rating' => 6,
		]);
		$this->assertUnprocessable($response);
		$response->assertJsonValidationErrors(['rating']);
	}

	public function testSetRatingWithoutAuthentication(): void
	{
		$response = $this->postJson('Photo::setRating', [
			'photo_id' => $this->photo1->id,
			'rating' => 5,
		]);
		$this->assertUnauthorized($response);
	}

	public function testSetRatingWithoutReadAccess(): void
	{
		// userMayUpload1 does not have access to photo2 (owned by userMayUpload2, in private album2)
		$response = $this->actingAs($this->userMayUpload1)->postJson('Photo::setRating', [
			'photo_id' => $this->photo2->id,
			'rating' => 5,
		]);
		$this->assertForbidden($response);
	}

	public function testSetRatingWithValidDataAsOwner(): void
	{
		$response = $this->actingAs($this->userMayUpload1)->postJson('Photo::setRating', [
			'photo_id' => $this->photo1->id,
			'rating' => 5,
		]);
		$this->assertCreated($response);
		$response->assertJsonStructure([
			'id',
			'title',
		]);
	}

	public function testSetRatingWithZeroRating(): void
	{
		// Rating 0 should be valid (removes rating - idempotent)
		$response = $this->actingAs($this->userMayUpload1)->postJson('Photo::setRating', [
			'photo_id' => $this->photo1->id,
			'rating' => 0,
		]);
		$this->assertCreated($response);
	}

	public function testSetRatingWithReadAccessViaPublicAlbum(): void
	{
		// photo4 is in a public album, so any authenticated user can rate it
		$response = $this->actingAs($this->userMayUpload1)->postJson('Photo::setRating', [
			'photo_id' => $this->photo4->id,
			'rating' => 4,
		]);
		$this->assertCreated($response);
	}
}
