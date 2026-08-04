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

namespace Tests\Unit\Models;

use App\Models\AlbumUserThumb;
use App\Models\Face;
use App\Models\Person;
use App\Models\PersonAlbum;
use App\Models\Photo;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\AbstractTestCase;

class PersonAlbumTest extends AbstractTestCase
{
	use DatabaseTransactions;

	private function makePersonAlbum(User $user, Person $person): PersonAlbum
	{
		// No PersonAlbumFactory exists; build it the same way
		// App\Actions\Album\CreatePersonAlbum does.
		$person_album = new PersonAlbum();
		$person_album->title = 'Test Person Album';
		$person_album->owner_id = $user->id;
		$person_album->is_and = false;
		$person_album->save();
		$person_album->persons()->attach($person->id);

		return $person_album;
	}

	public function testThumbUsesEagerLoadedUserThumbRowWhenPresent(): void
	{
		$user = User::factory()->create();
		$person = Person::factory()->create();
		$cached_photo = Photo::factory()->owned_by($user)->create();
		$person_album = $this->makePersonAlbum($user, $person);

		AlbumUserThumb::query()->create([
			'user_id' => null,
			'album_id' => $person_album->id,
			'photo_id' => $cached_photo->id,
		]);

		$loaded = PersonAlbum::query()->with('userThumbRow.photo.size_variants')->find($person_album->id);

		self::assertEquals($cached_photo->id, $loaded->thumb->id);
	}

	public function testThumbComputesLiveAndSeedsCacheWhenNoCacheRow(): void
	{
		$user = User::factory()->create();
		$person = Person::factory()->create();
		$photo = Photo::factory()->owned_by($user)->create();
		Face::factory()->for_photo($photo)->for_person($person)->create();
		$person_album = $this->makePersonAlbum($user, $person);

		self::assertDatabaseMissing('album_user_thumbs', ['album_id' => $person_album->id]);

		$thumb = $person_album->thumb;

		self::assertNotNull($thumb);
		self::assertEquals($photo->id, $thumb->id);
		self::assertDatabaseHas('album_user_thumbs', ['album_id' => $person_album->id, 'photo_id' => $photo->id]);
	}

	public function testThumbIsNullWhenPersonHasNoPhotos(): void
	{
		$user = User::factory()->create();
		$person = Person::factory()->create();
		$person_album = $this->makePersonAlbum($user, $person);

		self::assertNull($person_album->thumb);
	}
}
