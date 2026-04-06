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

use App\Models\Album;
use App\Models\Configs;
use App\Models\Face;
use App\Models\Person;
use App\Models\Photo;
use Illuminate\Support\Facades\DB;
use Tests\Feature_v2\Base\BaseApiWithDataTest;

class AlbumPeopleTest extends BaseApiWithDataTest
{
	private Album $testAlbum1;
	private Album $testAlbum2;
	private Photo $testPhoto1;
	private Photo $testPhoto2;
	private Photo $testPhoto3;
	private Person $testPerson1;
	private Person $testPerson2;
	private Person $testPerson3;

	public function setUp(): void
	{
		parent::setUp();
		$this->requireSe();

		Configs::set('ai_vision_enabled', '1');
		Configs::set('ai_vision_face_enabled', '1');
		Configs::set('ai_vision_face_permission_mode', 'public');

		// Create albums
		$this->testAlbum1 = Album::factory()->owned_by($this->userMayUpload1)->create();
		$this->testAlbum2 = Album::factory()->owned_by($this->userMayUpload1)->create();

		// Create photos
		$this->testPhoto1 = Photo::factory()->owned_by($this->userMayUpload1)->create();
		$this->testPhoto2 = Photo::factory()->owned_by($this->userMayUpload1)->create();
		$this->testPhoto3 = Photo::factory()->owned_by($this->userMayUpload1)->create();

		// Add photos to albums
		DB::table('photo_album')->insert([
			['photo_id' => $this->testPhoto1->id, 'album_id' => $this->testAlbum1->id],
			['photo_id' => $this->testPhoto2->id, 'album_id' => $this->testAlbum1->id],
			['photo_id' => $this->testPhoto3->id, 'album_id' => $this->testAlbum2->id],
		]);

		// Create persons
		$this->testPerson1 = Person::factory()->with_name('Alice')->create(['is_searchable' => true]);
		$this->testPerson2 = Person::factory()->with_name('Bob')->create(['is_searchable' => true]);
		$this->testPerson3 = Person::factory()->with_name('Charlie')->create(['is_searchable' => false]);

		// Create faces in album1 photos
		Face::factory()->for_photo($this->testPhoto1)->without_crop()->create([
			'person_id' => $this->testPerson1->id,
			'is_dismissed' => false,
		]);
		Face::factory()->for_photo($this->testPhoto1)->without_crop()->create([
			'person_id' => $this->testPerson2->id,
			'is_dismissed' => false,
		]);
		Face::factory()->for_photo($this->testPhoto2)->without_crop()->create([
			'person_id' => $this->testPerson1->id,
			'is_dismissed' => false,
		]);

		// Create faces in album2 photos
		Face::factory()->for_photo($this->testPhoto3)->without_crop()->create([
			'person_id' => $this->testPerson3->id,
			'is_dismissed' => false,
		]);
	}

	public function tearDown(): void
	{
		DB::table('photo_album')->delete();
		DB::table('faces')->delete();
		DB::table('persons')->delete();
		$this->resetSe();
		parent::tearDown();
	}

	// ── GET ALBUM PEOPLE ──────────────────────────────────────────

	public function testGetAlbumPeopleReturnsDistinctPersons(): void
	{
		$response = $this->actingAs($this->userMayUpload1)->getJson('Album/' . $this->testAlbum1->id . '/people');
		$this->assertStatus($response, 200);

		$people = $response->json('data');
		self::assertCount(2, $people); // Alice and Bob, not Charlie (different album)

		$names = array_column($people, 'name');
		self::assertContains('Alice', $names);
		self::assertContains('Bob', $names);
		self::assertNotContains('Charlie', $names);
	}

	public function testGetAlbumPeopleDeduplicates(): void
	{
		// Alice appears in two photos in album1, should appear only once
		$response = $this->actingAs($this->userMayUpload1)->getJson('Album/' . $this->testAlbum1->id . '/people');
		$this->assertStatus($response, 200);

		$people = $response->json('data');
		$alice_count = count(array_filter($people, fn ($p) => $p['name'] === 'Alice'));
		self::assertEquals(1, $alice_count);
	}

	public function testGetAlbumPeopleExcludesDismissedFaces(): void
	{
		// Dismiss all faces of person2 in album1
		Face::where('photo_id', $this->testPhoto1->id)
			->where('person_id', $this->testPerson2->id)
			->update(['is_dismissed' => true]);

		$response = $this->actingAs($this->userMayUpload1)->getJson('Album/' . $this->testAlbum1->id . '/people');
		$this->assertStatus($response, 200);

		$people = $response->json('data');
		$names = array_column($people, 'name');
		self::assertContains('Alice', $names);
		self::assertNotContains('Bob', $names); // dismissed
	}

	public function testGetAlbumPeopleExcludesNonSearchableForGuests(): void
	{
		// Charlie is not searchable, should not appear for guest users
		// Add Charlie to album1
		Face::factory()->for_photo($this->testPhoto1)->without_crop()->create([
			'person_id' => $this->testPerson3->id,
			'is_dismissed' => false,
		]);

		// Make album public
		$this->testAlbum1->public = true;
		$this->testAlbum1->save();

		$response = $this->getJson('Album/' . $this->testAlbum1->id . '/people');
		$this->assertStatus($response, 200);

		$people = $response->json('data');
		$names = array_column($people, 'name');
		self::assertContains('Alice', $names);
		self::assertContains('Bob', $names);
		self::assertNotContains('Charlie', $names); // not searchable
	}

	public function testGetAlbumPeopleIncludesNonSearchableForAdmin(): void
	{
		// Charlie is not searchable, but admins should see them
		// Add Charlie to album1
		Face::factory()->for_photo($this->testPhoto1)->without_crop()->create([
			'person_id' => $this->testPerson3->id,
			'is_dismissed' => false,
		]);

		$response = $this->actingAs($this->admin)->getJson('Album/' . $this->testAlbum1->id . '/people');
		$this->assertStatus($response, 200);

		$people = $response->json('data');
		$names = array_column($people, 'name');
		self::assertContains('Alice', $names);
		self::assertContains('Bob', $names);
		self::assertContains('Charlie', $names); // admin sees all
	}

	public function testGetAlbumPeopleRequiresAlbumAccess(): void
	{
		// userNoUpload2 has no access to album1
		$response = $this->actingAs($this->userNoUpload2)->getJson('Album/' . $this->testAlbum1->id . '/people');
		$this->assertStatus($response, [403, 404]);
	}

	public function testGetAlbumPeopleReturnsEmptyForAlbumWithoutFaces(): void
	{
		// Create empty album
		$empty_album = Album::factory()->owned_by($this->userMayUpload1)->create();

		$response = $this->actingAs($this->userMayUpload1)->getJson('Album/' . $empty_album->id . '/people');
		$this->assertStatus($response, 200);

		$people = $response->json('data');
		self::assertCount(0, $people);
	}

	public function testGetAlbumPeopleReturnsEmptyForAlbumWithOnlyUnassignedFaces(): void
	{
		// Create photo with unassigned face
		$photo_unassigned = Photo::factory()->owned_by($this->userMayUpload1)->create();
		DB::table('photo_album')->insert([
			'photo_id' => $photo_unassigned->id,
			'album_id' => $this->testAlbum1->id,
		]);
		Face::factory()->for_photo($photo_unassigned)->without_crop()->create([
			'person_id' => null,
			'is_dismissed' => false,
		]);

		$response = $this->actingAs($this->userMayUpload1)->getJson('Album/' . $this->testAlbum1->id . '/people');
		$this->assertStatus($response, 200);

		// Should still return Alice and Bob, not the unassigned face
		$people = $response->json('data');
		self::assertCount(2, $people);
	}
}
