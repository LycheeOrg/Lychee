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

class PersonTest extends AbstractTestCase
{
	use DatabaseTransactions;

	public function testPersonUserRelationship(): void
	{
		$user = User::factory()->may_upload()->create();
		$person = Person::factory()->linked_to($user)->create();

		self::assertNotNull($person->user);
		self::assertEquals($user->id, $person->user->id);
	}

	public function testPersonFacesRelationship(): void
	{
		$user = User::factory()->may_upload()->create();
		$photo = Photo::factory()->owned_by($user)->create();
		$person = Person::factory()->create();

		$face1 = Face::factory()->for_photo($photo)->for_person($person)->create();
		$face2 = Face::factory()->for_photo($photo)->for_person($person)->create();

		$person->refresh();
		self::assertCount(2, $person->faces);
		self::assertTrue($person->faces->contains($face1));
		self::assertTrue($person->faces->contains($face2));
	}

	public function testPersonRepresentativeFaceRelationship(): void
	{
		$user = User::factory()->may_upload()->create();
		$photo = Photo::factory()->owned_by($user)->create();
		$person = Person::factory()->create();
		$face = Face::factory()->for_photo($photo)->for_person($person)->create();

		$person->representative_face_id = $face->id;
		$person->save();
		$person->refresh();

		self::assertNotNull($person->representativeFace);
		self::assertEquals($face->id, $person->representativeFace->id);
	}

	public function testPersonDeleteNullifiesFaces(): void
	{
		$user = User::factory()->may_upload()->create();
		$photo = Photo::factory()->owned_by($user)->create();
		$person = Person::factory()->create();
		$face = Face::factory()->for_photo($photo)->for_person($person)->create();

		self::assertEquals($person->id, $face->person_id);

		$person->delete();

		$face->refresh();
		self::assertNull($face->person_id);
	}

	public function testPhotoDeleteCascadesFaces(): void
	{
		$user = User::factory()->may_upload()->create();
		$photo = Photo::factory()->owned_by($user)->create();
		$person = Person::factory()->create();
		$face = Face::factory()->for_photo($photo)->for_person($person)->create();
		$face_id = $face->id;

		// Use DB-level delete to trigger cascade FK without model observers
		\Illuminate\Support\Facades\DB::table('photos')->where('id', '=', $photo->id)->delete();

		self::assertNull(Face::find($face_id));
	}

	public function testScopeSearchable(): void
	{
		Person::factory()->with_name('Visible')->create();
		Person::factory()->with_name('Hidden')->not_searchable()->create();

		$searchable = Person::searchable()->get();
		self::assertCount(1, $searchable);
		self::assertEquals('Visible', $searchable->first()->name);
	}

	public function testNullableUserRelationship(): void
	{
		$person = Person::factory()->create();
		self::assertNull($person->user);
		self::assertNull($person->user_id);
	}

	public function testCastsAreCorrect(): void
	{
		$person = Person::factory()->create();
		self::assertIsBool($person->is_searchable);
	}
}
