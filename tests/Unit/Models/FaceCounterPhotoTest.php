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

/**
 * Tests for the denormalized face_count counter on Photo.
 *
 * face_count represents the number of non-dismissed faces detected on a photo.
 * The FaceObserver is responsible for keeping this column up-to-date.
 */
class FaceCounterPhotoTest extends AbstractTestCase
{
	use DatabaseTransactions;

	private User $user;
	private Photo $photo;

	public function setUp(): void
	{
		parent::setUp();
		$this->user = User::factory()->may_upload()->create();
		$this->photo = Photo::factory()->owned_by($this->user)->create();
	}

	// ── (a) creating a non-dismissed face increments photo.face_count ─────

	public function testCreatingActiveFaceIncrementsPhotoFaceCount(): void
	{
		Face::factory()->for_photo($this->photo)->create();

		$this->photo->refresh();
		self::assertEquals(1, $this->photo->face_count);
	}

	// ── (b) creating a dismissed face does NOT increment photo.face_count ─

	public function testCreatingDismissedFaceDoesNotIncrementPhotoFaceCount(): void
	{
		Face::factory()->for_photo($this->photo)->dismissed()->create();

		$this->photo->refresh();
		self::assertEquals(0, $this->photo->face_count);
	}

	// ── (c) dismissing a previously non-dismissed face decrements count ───

	public function testDismissingActiveFaceDecrementsPhotoFaceCount(): void
	{
		$face = Face::factory()->for_photo($this->photo)->create();

		$this->photo->refresh();
		self::assertEquals(1, $this->photo->face_count);

		$face->is_dismissed = true;
		$face->save();

		$this->photo->refresh();
		self::assertEquals(0, $this->photo->face_count);
	}

	// ── (d) undismissing a face increments photo.face_count ──────────────

	public function testUndismissingFaceIncrementsPhotoFaceCount(): void
	{
		$face = Face::factory()->for_photo($this->photo)->dismissed()->create();

		$this->photo->refresh();
		self::assertEquals(0, $this->photo->face_count);

		$face->is_dismissed = false;
		$face->save();

		$this->photo->refresh();
		self::assertEquals(1, $this->photo->face_count);
	}

	// ── (e) deleting a non-dismissed face decrements photo.face_count ─────

	public function testDeletingActiveFaceDecrementsPhotoFaceCount(): void
	{
		$face = Face::factory()->for_photo($this->photo)->create();

		$this->photo->refresh();
		self::assertEquals(1, $this->photo->face_count);

		$face->delete();

		$this->photo->refresh();
		self::assertEquals(0, $this->photo->face_count);
	}

	// ── (f) deleting a dismissed face leaves photo.face_count unchanged ───

	public function testDeletingDismissedFaceLeavesPhotoFaceCountUnchanged(): void
	{
		// Create one active face so face_count is non-zero
		Face::factory()->for_photo($this->photo)->create();
		$dismissed = Face::factory()->for_photo($this->photo)->dismissed()->create();

		$this->photo->refresh();
		$count_before = $this->photo->face_count;

		$dismissed->delete();

		$this->photo->refresh();
		self::assertEquals($count_before, $this->photo->face_count);
	}

	// ── (g) changing person_id does NOT affect photo.face_count ──────────

	public function testChangingPersonIdDoesNotAffectPhotoFaceCount(): void
	{
		$person = Person::factory()->create();
		$face = Face::factory()->for_photo($this->photo)->create();

		$this->photo->refresh();
		$count_before = $this->photo->face_count;

		$face->person_id = $person->id;
		$face->save();

		$this->photo->refresh();
		self::assertEquals($count_before, $this->photo->face_count);
	}
}
