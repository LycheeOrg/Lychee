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

namespace Tests\Unit\Actions\Album;

use App\Actions\Album\SetProtectionPolicy;
use App\Events\AlbumSaved;
use App\Events\PersonAlbumSaved;
use App\Events\TagAlbumSaved;
use App\Http\Resources\Models\Utils\AlbumProtectionPolicy;
use App\Models\Album;
use App\Models\PersonAlbum;
use App\Models\TagAlbum;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Event;
use Tests\AbstractTestCase;

class SetProtectionPolicyTest extends AbstractTestCase
{
	use DatabaseTransactions;

	private function policy(bool $is_public): AlbumProtectionPolicy
	{
		return new AlbumProtectionPolicy(
			is_public: $is_public,
			is_link_required: false,
			is_nsfw: false,
			grants_full_photo_access: false,
			grants_download: false,
			grants_upload: false,
		);
	}

	public function testDispatchesAlbumSavedForRegularAlbum(): void
	{
		Event::fake([AlbumSaved::class, TagAlbumSaved::class, PersonAlbumSaved::class]);

		$user = User::factory()->create();
		$album = Album::factory()->as_root()->owned_by($user)->create();

		(new SetProtectionPolicy())->do($album, $this->policy(true), false, null);

		Event::assertDispatched(AlbumSaved::class, fn (AlbumSaved $e) => in_array($album->id, $e->album_ids));
		Event::assertNotDispatched(TagAlbumSaved::class);
		Event::assertNotDispatched(PersonAlbumSaved::class);
	}

	public function testDoesNotThrowAndDispatchesTagAlbumSavedForTagAlbum(): void
	{
		Event::fake([AlbumSaved::class, TagAlbumSaved::class, PersonAlbumSaved::class]);

		$user = User::factory()->create();
		$album = TagAlbum::factory()->owned_by($user)->create();

		// Prior to the fix this call throws a TypeError, since AlbumSaved's
		// constructor is typed `Album`.
		(new SetProtectionPolicy())->do($album, $this->policy(true), false, null);

		Event::assertDispatched(TagAlbumSaved::class, fn (TagAlbumSaved $e) => $e->tag_album_ids === [$album->id]);
		Event::assertNotDispatched(AlbumSaved::class);
		Event::assertNotDispatched(PersonAlbumSaved::class);
	}

	public function testDoesNotThrowAndDispatchesTagAlbumSavedForTagAlbumWhenNotPublic(): void
	{
		Event::fake([AlbumSaved::class, TagAlbumSaved::class, PersonAlbumSaved::class]);

		$user = User::factory()->create();
		$album = TagAlbum::factory()->owned_by($user)->create();

		(new SetProtectionPolicy())->do($album, $this->policy(false), false, null);

		Event::assertDispatched(TagAlbumSaved::class, fn (TagAlbumSaved $e) => $e->tag_album_ids === [$album->id]);
		Event::assertNotDispatched(AlbumSaved::class);
	}

	public function testDoesNotThrowAndDispatchesPersonAlbumSavedForPersonAlbum(): void
	{
		Event::fake([AlbumSaved::class, TagAlbumSaved::class, PersonAlbumSaved::class]);

		$user = User::factory()->create();
		// No PersonAlbumFactory exists; build it the same way
		// App\Actions\Album\CreatePersonAlbum does.
		$album = new PersonAlbum();
		$album->title = 'Test Person Album';
		$album->owner_id = $user->id;
		$album->is_and = true;
		$album->save();

		(new SetProtectionPolicy())->do($album, $this->policy(true), false, null);

		Event::assertDispatched(PersonAlbumSaved::class, fn (PersonAlbumSaved $e) => $e->person_album->id === $album->id);
		Event::assertNotDispatched(AlbumSaved::class);
		Event::assertNotDispatched(TagAlbumSaved::class);
	}
}
