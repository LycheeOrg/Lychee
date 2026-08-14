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

namespace Tests\Feature_v2\Album;

use App\Events\PersonAlbumSaved;
use App\Events\TagAlbumSaved;
use App\Models\Configs;
use App\Models\Person;
use Illuminate\Support\Facades\Event;
use Tests\Feature_v2\Base\BaseApiWithDataTest;

class AlbumTagPersonSavedEventTest extends BaseApiWithDataTest
{
	public function testCreateTagAlbumDispatchesTagAlbumSavedEvent(): void
	{
		Event::fake([TagAlbumSaved::class]);

		$response = $this->actingAs($this->userMayUpload1)->postJson('TagAlbum', [
			'title' => 'test_tag_saved_event',
			'tags' => ['tag1'],
			'is_and' => '1',
		]);
		$this->assertOk($response);
		$album_id = $response->getOriginalContent();

		Event::assertDispatched(TagAlbumSaved::class, fn (TagAlbumSaved $e) => $e->tag_album_ids === [$album_id]);
	}

	public function testUpdateTagAlbumDispatchesTagAlbumSavedEvent(): void
	{
		Event::fake([TagAlbumSaved::class]);

		$response = $this->actingAs($this->userMayUpload1)->patchJson('TagAlbum', [
			'album_id' => $this->tagAlbum1->id,
			'title' => 'title',
			'tags' => ['tag1', 'tag2'],
			'description' => '',
			'photo_sorting_column' => 'title',
			'photo_sorting_order' => 'ASC',
			'copyright' => '',
			'is_pinned' => false,
			'is_and' => true,
			'photo_layout' => null,
			'photo_timeline' => null,
		]);
		$this->assertOk($response);

		Event::assertDispatched(TagAlbumSaved::class, fn (TagAlbumSaved $e) => $e->tag_album_ids === [$this->tagAlbum1->id]);
	}

	public function testCreatePersonAlbumDispatchesPersonAlbumSavedEvent(): void
	{
		Configs::set('ai_vision_enabled', '1');
		Configs::set('ai_vision_face_enabled', '1');
		$person = Person::factory()->create(['name' => 'Alice', 'is_searchable' => true]);

		Event::fake([PersonAlbumSaved::class]);

		$response = $this->actingAs($this->userMayUpload1)->postJson('PersonAlbum', [
			'title' => 'test_person_saved_event',
			'persons' => [$person->id],
			'is_and' => false,
		]);
		$this->assertOk($response);
		$album_id = $response->getOriginalContent();

		Event::assertDispatched(PersonAlbumSaved::class, fn (PersonAlbumSaved $e) => $e->person_album->id === $album_id);
	}

	public function testUpdatePersonAlbumDispatchesPersonAlbumSavedEvent(): void
	{
		Configs::set('ai_vision_enabled', '1');
		Configs::set('ai_vision_face_enabled', '1');
		$person = Person::factory()->create(['name' => 'Alice', 'is_searchable' => true]);

		$response = $this->actingAs($this->userMayUpload1)->postJson('PersonAlbum', [
			'title' => 'test_person_saved_event',
			'persons' => [$person->id],
			'is_and' => false,
		]);
		$this->assertOk($response);
		$album_id = $response->getOriginalContent();

		Event::fake([PersonAlbumSaved::class]);

		$response = $this->actingAs($this->userMayUpload1)->patchJson('PersonAlbum', [
			'album_id' => $album_id,
			'title' => 'updated_person_album',
			'description' => '',
			'persons' => [$person->id],
			'is_and' => true,
			'photo_sorting_column' => null,
			'photo_sorting_order' => null,
			'copyright' => null,
			'photo_layout' => null,
			'photo_timeline' => null,
			'is_pinned' => false,
		]);
		$this->assertOk($response);

		Event::assertDispatched(PersonAlbumSaved::class, fn (PersonAlbumSaved $e) => $e->person_album->id === $album_id);
	}
}
