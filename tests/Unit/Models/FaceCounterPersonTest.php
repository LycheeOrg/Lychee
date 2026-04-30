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
 * Tests for the denormalized counter columns on Person:
 *   - face_count:  non-dismissed faces assigned to this person
 *   - photo_count: distinct photos with non-dismissed faces for this person
 *
 * The FaceObserver is responsible for keeping these columns up-to-date.
 */
class FaceCounterPersonTest extends AbstractTestCase
{
	use DatabaseTransactions;

	private User $user;
	private Photo $photo;
	private Person $person;

	public function setUp(): void
	{
		parent::setUp();
		$this->user = User::factory()->may_upload()->create();
		$this->photo = Photo::factory()->owned_by($this->user)->create();
		$this->person = Person::factory()->create();
	}

	// ── (a) creating a non-dismissed face with person_id ──────────────────

	public function testCreatingActiveFaceIncrementsPersonCounters(): void
	{
		Face::factory()->for_photo($this->photo)->for_person($this->person)->create();

		$this->person->refresh();
		self::assertEquals(1, $this->person->face_count);
		self::assertEquals(1, $this->person->photo_count);
	}

	// ── (b) second face for the SAME person+photo ─────────────────────────

	public function testSecondFaceOnSamePhotoIncrementsFaceCountOnly(): void
	{
		Face::factory()->for_photo($this->photo)->for_person($this->person)->create();
		Face::factory()->for_photo($this->photo)->for_person($this->person)->create();

		$this->person->refresh();
		self::assertEquals(2, $this->person->face_count);
		// photo_count must still be 1 (same photo)
		self::assertEquals(1, $this->person->photo_count);
	}

	// ── (c) dismissing one face leaves photo_count unchanged ─────────────

	public function testDismissingOneFaceOfTwoDecrementsFaceCountOnly(): void
	{
		$face1 = Face::factory()->for_photo($this->photo)->for_person($this->person)->create();
		Face::factory()->for_photo($this->photo)->for_person($this->person)->create();

		$face1->is_dismissed = true;
		$face1->save();

		$this->person->refresh();
		// face_count decremented by 1 (dismissed face no longer counted)
		self::assertEquals(1, $this->person->face_count);
		// photo_count unchanged — the other face still active on same photo
		self::assertEquals(1, $this->person->photo_count);
	}

	// ── (d) dismissing the LAST face for a person+photo ─────────────────

	public function testDismissingLastFaceDecrementsBothCounters(): void
	{
		$face = Face::factory()->for_photo($this->photo)->for_person($this->person)->create();

		$face->is_dismissed = true;
		$face->save();

		$this->person->refresh();
		self::assertEquals(0, $this->person->face_count);
		self::assertEquals(0, $this->person->photo_count);
	}

	// ── (e) undismissing a face re-increments the relevant counters ───────

	public function testUndismissingFaceReincrementsCounters(): void
	{
		$face = Face::factory()->for_photo($this->photo)->for_person($this->person)->dismissed()->create();

		// dismissed face should not have been counted by the observer
		$this->person->refresh();
		self::assertEquals(0, $this->person->face_count);
		self::assertEquals(0, $this->person->photo_count);

		$face->is_dismissed = false;
		$face->save();

		$this->person->refresh();
		self::assertEquals(1, $this->person->face_count);
		self::assertEquals(1, $this->person->photo_count);
	}

	// ── (f) deleting a non-dismissed face decrements the counters ─────────

	public function testDeletingActiveFaceDecrementsPersonCounters(): void
	{
		$face = Face::factory()->for_photo($this->photo)->for_person($this->person)->create();

		$face->delete();

		$this->person->refresh();
		self::assertEquals(0, $this->person->face_count);
		self::assertEquals(0, $this->person->photo_count);
	}

	// ── (g) deleting a dismissed face leaves counters unchanged ──────────

	public function testDeletingDismissedFaceLeavesPersonCountersUnchanged(): void
	{
		// Create one active face so counters are non-zero
		Face::factory()->for_photo($this->photo)->for_person($this->person)->create();
		$dismissed = Face::factory()->for_photo($this->photo)->for_person($this->person)->dismissed()->create();

		$this->person->refresh();
		$face_count_before = $this->person->face_count;
		$photo_count_before = $this->person->photo_count;

		$dismissed->delete();

		$this->person->refresh();
		self::assertEquals($face_count_before, $this->person->face_count);
		self::assertEquals($photo_count_before, $this->person->photo_count);
	}

	// ── (h) unassigning a face decrements counters for the old person ─────

	public function testUnassigningFaceDecrementsOldPersonCounters(): void
	{
		$face = Face::factory()->for_photo($this->photo)->for_person($this->person)->create();

		$this->person->refresh();
		self::assertEquals(1, $this->person->face_count);

		$face->person_id = null;
		$face->save();

		$this->person->refresh();
		self::assertEquals(0, $this->person->face_count);
		self::assertEquals(0, $this->person->photo_count);
	}

	// ── reassigning a face from one person to another ─────────────────────

	public function testReassigningFaceUpdatesCountersOnBothPersons(): void
	{
		$person2 = Person::factory()->create();
		$face = Face::factory()->for_photo($this->photo)->for_person($this->person)->create();

		$face->person_id = $person2->id;
		$face->save();

		$this->person->refresh();
		self::assertEquals(0, $this->person->face_count);
		self::assertEquals(0, $this->person->photo_count);

		$person2->refresh();
		self::assertEquals(1, $person2->face_count);
		self::assertEquals(1, $person2->photo_count);
	}
}
