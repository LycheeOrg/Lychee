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

namespace Tests\Unit\Models;

use App\Models\Face;
use App\Models\Person;
use App\Models\Photo;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\AbstractTestCase;

class FaceTest extends AbstractTestCase
{
	use DatabaseTransactions;

	public function testFacePhotoRelationship(): void
	{
		$user = User::factory()->may_upload()->create();
		$photo = Photo::factory()->owned_by($user)->create();
		$face = Face::factory()->for_photo($photo)->create();

		self::assertNotNull($face->photo);
		self::assertEquals($photo->id, $face->photo->id);
	}

	public function testFacePersonRelationship(): void
	{
		$user = User::factory()->may_upload()->create();
		$photo = Photo::factory()->owned_by($user)->create();
		$person = Person::factory()->create();
		$face = Face::factory()->for_photo($photo)->for_person($person)->create();

		self::assertNotNull($face->person);
		self::assertEquals($person->id, $face->person->id);
	}

	public function testFaceNullablePersonRelationship(): void
	{
		$user = User::factory()->may_upload()->create();
		$photo = Photo::factory()->owned_by($user)->create();
		$face = Face::factory()->for_photo($photo)->create();

		self::assertNull($face->person);
		self::assertNull($face->person_id);
	}

	public function testBoundingBoxValidation(): void
	{
		$user = User::factory()->may_upload()->create();
		$photo = Photo::factory()->owned_by($user)->create();
		$face = Face::factory()->for_photo($photo)->create();

		self::assertIsFloat($face->x);
		self::assertIsFloat($face->y);
		self::assertIsFloat($face->width);
		self::assertIsFloat($face->height);
		self::assertGreaterThanOrEqual(0.0, $face->x);
		self::assertLessThanOrEqual(1.0, $face->x);
		self::assertGreaterThanOrEqual(0.0, $face->y);
		self::assertLessThanOrEqual(1.0, $face->y);
	}

	public function testCropUrlAccessorWithToken(): void
	{
		$user = User::factory()->may_upload()->create();
		$photo = Photo::factory()->owned_by($user)->create();
		$face = Face::factory()->for_photo($photo)->create();

		$tok = $face->crop_token;
		self::assertNotNull($face->crop_url);
		self::assertEquals(
			'uploads/faces/' . substr($tok, 0, 2) . '/' . substr($tok, 2, 2) . '/' . $tok . '.jpg',
			$face->crop_url
		);
	}

	public function testCropUrlAccessorWithoutToken(): void
	{
		$user = User::factory()->may_upload()->create();
		$photo = Photo::factory()->owned_by($user)->create();
		$face = Face::factory()->for_photo($photo)->without_crop()->create();

		self::assertNull($face->crop_url);
	}

	public function testDismissedCast(): void
	{
		$user = User::factory()->may_upload()->create();
		$photo = Photo::factory()->owned_by($user)->create();
		$face = Face::factory()->for_photo($photo)->create();

		self::assertIsBool($face->is_dismissed);
		self::assertFalse($face->is_dismissed);

		$dismissed = Face::factory()->for_photo($photo)->dismissed()->create();
		self::assertTrue($dismissed->is_dismissed);
	}

	public function testClusterLabelCast(): void
	{
		$user = User::factory()->may_upload()->create();
		$photo = Photo::factory()->owned_by($user)->create();
		$face = Face::factory()->for_photo($photo)->with_cluster(5)->create();

		self::assertIsInt($face->cluster_label);
		self::assertEquals(5, $face->cluster_label);
	}

	public function testConfidenceCast(): void
	{
		$user = User::factory()->may_upload()->create();
		$photo = Photo::factory()->owned_by($user)->create();
		$face = Face::factory()->for_photo($photo)->with_confidence(0.95)->create();

		self::assertIsFloat($face->confidence);
		self::assertEqualsWithDelta(0.95, $face->confidence, 0.001);
	}
}
