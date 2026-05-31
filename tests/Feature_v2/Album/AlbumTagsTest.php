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

use App\Models\Tag;
use Tests\Feature_v2\Base\BaseApiWithDataTest;

class AlbumTagsTest extends BaseApiWithDataTest
{
	public function testOwnerCanSetAlbumTags(): void
	{
		$response = $this->actingAs($this->userMayUpload1)->patchJson('Album::albumTags', [
			'album_id' => $this->album1->id,
			'tags' => ['skiing', 'mountains'],
		]);
		$this->assertNoContent($response);

		$tag_names = $this->album1->fresh()->tags->pluck('name')->sort()->values()->all();
		self::assertSame(['mountains', 'skiing'], $tag_names);
	}

	public function testTagsArePersistedAndReturnedInEditableResource(): void
	{
		$this->actingAs($this->userMayUpload1)->patchJson('Album::albumTags', [
			'album_id' => $this->album1->id,
			'tags' => ['mountains', 'skiing'],
		]);

		$response = $this->actingAs($this->userMayUpload1)->getJsonWithData('Album::head', ['album_id' => $this->album1->id]);
		$this->assertOk($response);

		$tag_names = $response->json('resource.editable.tags');
		sort($tag_names);
		self::assertSame(['mountains', 'skiing'], $tag_names);
	}

	public function testUnauthenticatedUserCannotSetAlbumTags(): void
	{
		$response = $this->patchJson('Album::albumTags', [
			'album_id' => $this->album1->id,
			'tags' => ['skiing'],
		]);

		$this->assertUnauthorized($response);
	}

	public function testForbiddenUserCannotSetAlbumTags(): void
	{
		$response = $this->actingAs($this->userLocked)->patchJson('Album::albumTags', [
			'album_id' => $this->album1->id,
			'tags' => ['skiing'],
		]);

		$this->assertForbidden($response);
	}

	public function testEmptyTagsArrayClearsAlbumTags(): void
	{
		$skiing = Tag::factory()->create(['name' => 'skiing']);
		$this->album1->tags()->sync([$this->tag_test->id, $skiing->id]);

		$response = $this->actingAs($this->userMayUpload1)->patchJson('Album::albumTags', [
			'album_id' => $this->album1->id,
			'tags' => [],
		]);
		$this->assertNoContent($response);

		self::assertCount(0, $this->album1->fresh()->tags);
	}

	public function testUnknownAlbumReturnsNotFound(): void
	{
		$response = $this->actingAs($this->userMayUpload1)->patchJson('Album::albumTags', [
			'album_id' => '000000000000000000000000',
			'tags' => ['skiing'],
		]);

		$this->assertNotFound($response);
	}
}
