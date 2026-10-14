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

namespace Tests\AssistedVision\People;

use App\Models\Configs;
use App\Models\Face;
use App\Models\Person;
use Illuminate\Support\Facades\DB;
use Tests\Feature_v3\Base\BaseApiWithDataTest;

/**
 * `GET /api/v3/People` (Feature 093, API-093-04, NFR-093-05, NFR-093-07).
 */
class PeopleListV3Test extends BaseApiWithDataTest
{
	private Person $alice;
	private Person $bob;
	private Person $hidden;

	public function setUp(): void
	{
		parent::setUp();
		config(['features.struct-of-array' => true]);

		Configs::set('ai_vision_enabled', '1');
		Configs::set('ai_vision_face_enabled', '1');
		Configs::set('ai_vision_face_permission_mode', 'public');

		$this->bob = Person::factory()->with_name('Bob')->create();
		$this->alice = Person::factory()->with_name('Alice')->create();
		$this->hidden = Person::factory()->with_name('Hidden')->not_searchable()->create();
	}

	public function tearDown(): void
	{
		DB::table('persons')->update(['representative_face_id' => null]);
		DB::table('face_suggestions')->delete();
		DB::table('faces')->delete();
		DB::table('persons')->delete();
		parent::tearDown();
	}

	public function testFlagOffIsForbidden(): void
	{
		config(['features.struct-of-array' => false]);
		$this->assertForbidden($this->actingAs($this->admin)->getJsonV3('People'));
	}

	public function testFaceRecognitionOffIsForbidden(): void
	{
		Configs::set('ai_vision_face_enabled', '0');
		$this->assertForbidden($this->actingAs($this->admin)->getJsonV3('People'));
	}

	public function testPrivacyPreservingUserIsForbidden(): void
	{
		Configs::set('ai_vision_face_permission_mode', 'privacy-preserving');
		$this->assertForbidden($this->actingAs($this->userMayUpload1)->getJsonV3('People'));
	}

	public function testGuestAllowedInPublicMode(): void
	{
		$response = $this->getJsonV3('People');
		$this->assertOk($response);
		self::assertSame(['Alice', 'Bob'], $response->json('names'));
	}

	public function testAdminSeesEveryPersonOrderedByName(): void
	{
		$response = $this->actingAs($this->admin)->getJsonV3('People');
		$this->assertOk($response);
		self::assertSame(['Alice', 'Bob', 'Hidden'], $response->json('names'));
		self::assertSame([$this->alice->id, $this->bob->id, $this->hidden->id], $response->json('ids'));
	}

	public function testUserSeesSearchableAndOwnPerson(): void
	{
		$own = Person::factory()->with_name('Me')->not_searchable()->linked_to($this->userMayUpload1)->create();

		$response = $this->actingAs($this->userMayUpload1)->getJsonV3('People');
		$this->assertOk($response);
		self::assertSame(['Alice', 'Bob', 'Me'], $response->json('names'));
		self::assertNotContains($this->hidden->id, $response->json('ids'));
		self::assertContains($own->id, $response->json('ids'));
	}

	public function testShapeWithFaceCountsAndCrops(): void
	{
		$face = Face::factory()->for_photo($this->photo1)->for_person($this->alice)->create();
		DB::table('persons')->where('id', '=', $this->alice->id)->update(['representative_face_id' => $face->id, 'face_count' => 3]);

		$response = $this->actingAs($this->admin)->getJsonV3('People');
		$this->assertOk($response);

		$response->assertExactJson([
			'ids' => [$this->alice->id, $this->bob->id, $this->hidden->id],
			'names' => ['Alice', 'Bob', 'Hidden'],
			'face_counts' => [3, 0, 0],
			'crop_urls' => [$face->crop_url, null, null],
		]);
	}

	public function testSingleQuery(): void
	{
		$this->actingAs($this->admin);
		$count = 0;
		DB::listen(function ($query) use (&$count): void {
			if (str_contains($query->sql, 'persons')) {
				$count++;
			}
		});

		$this->assertOk($this->getJsonV3('People'));
		self::assertSame(1, $count);
	}
}
