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

namespace Tests\AssistedVision;

use App\Models\Configs;
use App\Models\Face;
use App\Models\Person;
use App\Models\PersonAlbum;
use App\Models\Statistics;
use Illuminate\Support\Facades\DB;
use Tests\Feature_v2\Base\BaseApiWithDataTest;

class PersonAlbumTest extends BaseApiWithDataTest
{
	protected Person $person1;
	protected Person $person2;

	public function setUp(): void
	{
		parent::setUp();

		config(['features.v8' => true]);
		Configs::set('ai_vision_enabled', '1');
		Configs::set('ai_vision_face_enabled', '1');

		DB::table('persons')->delete();
		$this->person1 = Person::factory()->create(['name' => 'Alice', 'is_searchable' => true]);
		$this->person2 = Person::factory()->create(['name' => 'Bob', 'is_searchable' => true]);
	}

	public function tearDown(): void
	{
		DB::table('person_albums_persons')->delete();
		DB::table('persons')->delete();
		config(['features.v8' => false]);

		parent::tearDown();
	}

	public function testCreatePersonAlbumV8Disabled(): void
	{
		config(['features.v8' => false]);

		$response = $this->actingAs($this->userMayUpload1)->postJson('PersonAlbum', [
			'title' => 'test_person_album',
			'persons' => [$this->person1->id],
			'is_and' => false,
		]);
		$this->assertForbidden($response);
	}

	public function testCreatePersonAlbumFaceDisabled(): void
	{
		Configs::set('ai_vision_face_enabled', '0');

		$response = $this->actingAs($this->userMayUpload1)->postJson('PersonAlbum', [
			'title' => 'test_person_album',
			'persons' => [$this->person1->id],
			'is_and' => false,
		]);
		$this->assertForbidden($response);
	}

	public function testCreatePersonAlbumUnauthorized(): void
	{
		$response = $this->postJson('PersonAlbum', [
			'title' => 'test_person_album',
			'persons' => [$this->person1->id],
			'is_and' => false,
		]);
		$this->assertUnauthorized($response);
	}

	public function testCreatePersonAlbumLockedUser(): void
	{
		$response = $this->actingAs($this->userLocked)->postJson('PersonAlbum', [
			'title' => 'test_person_album',
			'persons' => [$this->person1->id],
			'is_and' => false,
		]);
		$this->assertForbidden($response);
	}

	public function testCreateUpdateDeletePersonAlbum(): void
	{
		$response = $this->actingAs($this->userMayUpload1)->postJson('PersonAlbum', [
			'title' => 'test_person_album',
			'persons' => [$this->person1->id],
			'is_and' => false,
		]);
		self::assertEquals(200, $response->getStatusCode());
		$album_id = $response->getOriginalContent();
		$this->assertEquals(1, Statistics::where('album_id', $album_id)->count());
		$this->assertDatabaseHas('person_albums', ['id' => $album_id]);
		$this->assertDatabaseHas('person_albums_persons', ['album_id' => $album_id, 'person_id' => $this->person1->id]);

		$response = $this->actingAs($this->userMayUpload1)->patchJson('PersonAlbum', [
			'album_id' => $album_id,
			'title' => 'updated_person_album',
			'description' => 'A description',
			'persons' => [$this->person1->id, $this->person2->id],
			'is_and' => true,
			'photo_sorting_column' => null,
			'photo_sorting_order' => null,
			'copyright' => null,
			'photo_layout' => null,
			'photo_timeline' => null,
			'is_pinned' => false,
		]);
		$this->assertOk($response);
		$this->assertDatabaseHas('person_albums_persons', ['album_id' => $album_id, 'person_id' => $this->person2->id]);

		$album = PersonAlbum::find($album_id);
		$this->assertEquals('updated_person_album', $album->title);
		$this->assertTrue($album->is_and);

		$response = $this->actingAs($this->userMayUpload1)->deleteJson('Album', ['album_ids' => [$album_id]]);
		$this->assertNoContent($response);
		$this->assertDatabaseMissing('person_albums', ['id' => $album_id]);
	}

	public function testRootListingIncludesPersonAlbums(): void
	{
		$response = $this->actingAs($this->userMayUpload1)->postJson('PersonAlbum', [
			'title' => 'listed_person_album',
			'persons' => [$this->person1->id],
			'is_and' => false,
		]);
		self::assertEquals(200, $response->getStatusCode());
		$album_id = $response->getOriginalContent();

		$response = $this->actingAs($this->userMayUpload1)->getJsonWithData('Albums');
		$this->assertOk($response);
		$response->assertJsonFragment(['id' => $album_id]);
	}

	public function testRootListingExcludesWhenFeatureDisabled(): void
	{
		$response = $this->actingAs($this->userMayUpload1)->postJson('PersonAlbum', [
			'title' => 'hidden_person_album',
			'persons' => [$this->person1->id],
			'is_and' => false,
		]);
		self::assertEquals(200, $response->getStatusCode());

		config(['features.v8' => false]);

		$response = $this->actingAs($this->userMayUpload1)->getJsonWithData('Albums');
		$this->assertOk($response);
		$person_albums = $response->json('person_albums') ?? [];
		$this->assertEmpty($person_albums);
	}

	public function testPersonAlbumORPhotoResolution(): void
	{
		Face::factory()->for_photo($this->photo1)->for_person($this->person1)->create();
		Face::factory()->for_photo($this->photo2)->for_person($this->person2)->create();

		$response = $this->actingAs($this->userMayUpload1)->postJson('PersonAlbum', [
			'title' => 'or_album',
			'persons' => [$this->person1->id, $this->person2->id],
			'is_and' => false,
		]);
		self::assertEquals(200, $response->getStatusCode());
		$album_id = $response->getOriginalContent();

		$album = PersonAlbum::find($album_id);
		$photos = $album->photos;
		$this->assertGreaterThanOrEqual(1, $photos->count());
	}

	public function testPersonAlbumANDPhotoResolution(): void
	{
		Face::factory()->for_photo($this->photo1)->for_person($this->person1)->create();
		Face::factory()->for_photo($this->photo1)->for_person($this->person2)->create();
		Face::factory()->for_photo($this->photo2)->for_person($this->person1)->create();

		$response = $this->actingAs($this->userMayUpload1)->postJson('PersonAlbum', [
			'title' => 'and_album',
			'persons' => [$this->person1->id, $this->person2->id],
			'is_and' => true,
		]);
		self::assertEquals(200, $response->getStatusCode());
		$album_id = $response->getOriginalContent();

		$album = PersonAlbum::find($album_id);
		$photos = $album->photos;
		$photo_ids = $photos->pluck('id')->all();
		$this->assertContains($this->photo1->id, $photo_ids);
		$this->assertNotContains($this->photo2->id, $photo_ids);
	}

	public function testPersonAlbumHeadEndpoint(): void
	{
		$response = $this->actingAs($this->userMayUpload1)->postJson('PersonAlbum', [
			'title' => 'head_test',
			'persons' => [$this->person1->id],
			'is_and' => false,
		]);
		$album_id = $response->getOriginalContent();

		$response = $this->actingAs($this->userMayUpload1)->getJsonWithData('Album::head', ['album_id' => $album_id]);
		$this->assertOk($response);
		$response->assertJsonFragment(['is_person_album' => true]);
		$response->assertJsonFragment(['title' => 'head_test']);
	}

	public function testPersonAlbumPhotoPagination(): void
	{
		Face::factory()->for_photo($this->photo1)->for_person($this->person1)->create();

		$response = $this->actingAs($this->userMayUpload1)->postJson('PersonAlbum', [
			'title' => 'paginate_test',
			'persons' => [$this->person1->id],
			'is_and' => false,
		]);
		$album_id = $response->getOriginalContent();

		$response = $this->actingAs($this->userMayUpload1)->getJsonWithData('Album::photos', ['album_id' => $album_id]);
		$this->assertOk($response);
	}

	public function testOrphanCleanupOnPersonDeletion(): void
	{
		$response = $this->actingAs($this->userMayUpload1)->postJson('PersonAlbum', [
			'title' => 'orphan_test',
			'persons' => [$this->person1->id],
			'is_and' => false,
		]);
		$album_id = $response->getOriginalContent();
		$this->assertDatabaseHas('person_albums', ['id' => $album_id]);

		$this->actingAs($this->admin)->deleteJson('Person/' . $this->person1->id);

		$this->person1 = Person::factory()->create(['name' => 'Alice_replacement', 'is_searchable' => true]);

		\App\Jobs\CleanupOrphanedPersonAlbumsJob::dispatchSync();

		$this->assertDatabaseMissing('person_albums', ['id' => $album_id]);
	}
}
