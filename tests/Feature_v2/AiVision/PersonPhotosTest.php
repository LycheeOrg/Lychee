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

namespace Tests\Feature_v2\AiVision;

use App\Models\Configs;
use App\Models\Face;
use App\Models\Person;
use Illuminate\Support\Facades\DB;
use Tests\Feature_v2\Base\BaseApiWithDataTest;

class PersonPhotosTest extends BaseApiWithDataTest
{
	private Person $person1;

	public function setUp(): void
	{
		parent::setUp();
		$this->requireSe();

		Configs::set('ai_vision_enabled', '1');
		Configs::set('ai_vision_face_enabled', '1');
		Configs::set('ai_vision_face_permission_mode', 'public');

		$this->person1 = Person::factory()->with_name('Alice')->create();
	}

	public function tearDown(): void
	{
		DB::table('face_suggestions')->delete();
		DB::table('faces')->delete();
		DB::table('persons')->delete();
		$this->resetSe();
		parent::tearDown();
	}

	public function testListPhotosForPerson(): void
	{
		Face::factory()->for_photo($this->photo1)->for_person($this->person1)->create();

		$response = $this->actingAs($this->admin)->getJson('Person/' . $this->person1->id . '/photos');
		$this->assertOk($response);

		$photo_ids = collect($response->json('photos'))->pluck('id')->all();
		self::assertContains($this->photo1->id, $photo_ids);
	}

	public function testEmptyResultForPersonWithNoFaces(): void
	{
		$response = $this->actingAs($this->admin)->getJson('Person/' . $this->person1->id . '/photos');
		$this->assertOk($response);
		self::assertEmpty($response->json('photos'));
	}

	public function testNextPreviousIdsAreSetRelativeToPersonCollection(): void
	{
		Face::factory()->for_photo($this->photo1)->for_person($this->person1)->create();
		Face::factory()->for_photo($this->photo2)->for_person($this->person1)->create();
		Face::factory()->for_photo($this->photo3)->for_person($this->person1)->create();

		$response = $this->actingAs($this->admin)->getJson('Person/' . $this->person1->id . '/photos');
		$this->assertOk($response);

		$photos = $response->json('photos');
		self::assertCount(3, $photos);

		// First photo: previous_photo_id must be null
		self::assertNull($photos[0]['previous_photo_id']);
		// First photo points forward to second
		self::assertSame($photos[1]['id'], $photos[0]['next_photo_id']);
		// Middle photo chained correctly
		self::assertSame($photos[0]['id'], $photos[1]['previous_photo_id']);
		self::assertSame($photos[2]['id'], $photos[1]['next_photo_id']);
		// Last photo: next_photo_id must be null
		self::assertNull($photos[2]['next_photo_id']);
		self::assertSame($photos[1]['id'], $photos[2]['previous_photo_id']);
	}

	public function testNonSearchablePersonForbiddenForNonOwner(): void
	{
		$hidden = Person::factory()->not_searchable()->create();

		$response = $this->actingAs($this->userMayUpload1)->getJson('Person/' . $hidden->id . '/photos');
		$this->assertForbidden($response);
	}

	public function testAdminCanViewPhotosOfNonSearchablePerson(): void
	{
		$hidden = Person::factory()->not_searchable()->create();
		Face::factory()->for_photo($this->photo1)->for_person($hidden)->create();

		$response = $this->actingAs($this->admin)->getJson('Person/' . $hidden->id . '/photos');
		$this->assertOk($response);
		self::assertNotEmpty($response->json('data'));
	}
}
