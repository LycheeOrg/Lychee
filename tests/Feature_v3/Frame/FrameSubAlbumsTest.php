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

namespace Tests\Feature_v3\Frame;

use App\Actions\Album\SyncRecursiveNsfw;
use App\Models\Album;
use App\Models\Configs;
use App\Models\Photo;
use App\Repositories\ConfigManager;
use Tests\Feature_v3\Base\BaseApiWithDataTest;

/**
 * Feature 088: album frames drawing from sub-albums (FR-088-16, FR-088-17,
 * S-088-14, S-088-15, S-088-21, S-088-22).
 */
class FrameSubAlbumsTest extends BaseApiWithDataTest
{
	private const KEYS = [
		'mod_frame_enabled',
		'mod_frame_include_subalbums',
		'hide_nsfw_in_frame',
	];

	/** @var array<string,string> */
	private array $initial_values = [];

	private Album $parent;
	private Album $child;

	public function setUp(): void
	{
		parent::setUp();
		foreach (self::KEYS as $key) {
			$this->initial_values[$key] = strval(Configs::query()->where('key', '=', $key)->value('value'));
		}
		Configs::set('mod_frame_enabled', '1');

		// A parent album without photos of its own, its only photos in a sub-album.
		$this->parent = Album::factory()->as_root()->owned_by($this->userMayUpload1)->with_title('Travels')->create();
		$this->child = Album::factory()->children_of($this->parent)->owned_by($this->userMayUpload1)->with_title('Iceland')->create();
		foreach (range(1, 6) as $i) {
			$photo = Photo::factory()->owned_by($this->userMayUpload1)->in($this->child)->create();
			$photo->title = 'Iceland ' . $i;
			$photo->save();
		}
	}

	public function tearDown(): void
	{
		foreach ($this->initial_values as $key => $value) {
			Configs::query()->where('key', '=', $key)->update(['value' => $value]);
		}
		resolve(ConfigManager::class)->invalidateCache();
		parent::tearDown();
	}

	public function testSubAlbumModeDrawsFromSubAlbumsAndNamesThem(): void
	{
		Configs::set('mod_frame_include_subalbums', '1');

		$response = $this->actingAs($this->userMayUpload1)->getJsonWithData('Frame', ['album_id' => $this->parent->id]);
		$this->assertOk($response);
		$response->assertJsonPath('photo.albums', ['Iceland']);
		self::assertStringStartsWith('Iceland ', $response->json('photo.title'));
	}

	public function testSubAlbumModeKeepsTheRandomOrder(): void
	{
		Configs::set('mod_frame_include_subalbums', '1');

		$titles = [];
		foreach (range(1, 10) as $_) {
			$response = $this->actingAs($this->userMayUpload1)->getJsonWithData('Frame', ['album_id' => $this->parent->id]);
			$this->assertOk($response);
			$titles[] = $response->json('photo.title');
		}

		// Six photos drawn ten times: all identical has a probability of about 1e-7.
		self::assertGreaterThan(1, count(array_unique($titles)));
	}

	public function testWithoutSubAlbumModeTheParentIsEmpty(): void
	{
		Configs::set('mod_frame_include_subalbums', '0');

		$response = $this->actingAs($this->userMayUpload1)->getJsonWithData('Frame', ['album_id' => $this->parent->id]);
		$this->assertStatus($response, 500);
		$response->assertSee('PhotoCollectionEmptyException');
	}

	public function testFrameButtonFollowsSubAlbumMode(): void
	{
		Configs::set('mod_frame_include_subalbums', '1');
		$response = $this->actingAs($this->userMayUpload1)->getJsonWithData('Album::head', ['album_id' => $this->parent->id]);
		$this->assertOk($response);
		$response->assertJsonPath('config.is_mod_frame_enabled', true);

		Configs::set('mod_frame_include_subalbums', '0');
		$response = $this->actingAs($this->userMayUpload1)->getJsonWithData('Album::head', ['album_id' => $this->parent->id]);
		$this->assertOk($response);
		$response->assertJsonPath('config.is_mod_frame_enabled', false);
	}

	public function testSubAlbumModeSkipsSensitiveSubAlbumsWhenHidden(): void
	{
		Configs::set('mod_frame_include_subalbums', '1');
		Configs::set('hide_nsfw_in_frame', '1');
		$this->child->is_nsfw = true;
		$this->child->save();
		(new SyncRecursiveNsfw())->do();

		$response = $this->actingAs($this->userMayUpload1)->getJsonWithData('Frame', ['album_id' => $this->parent->id]);
		$this->assertStatus($response, 500);
		$response->assertSee('PhotoCollectionEmptyException');

		$response = $this->actingAs($this->userMayUpload1)->getJsonWithData('Album::head', ['album_id' => $this->parent->id]);
		$this->assertOk($response);
		$response->assertJsonPath('config.is_mod_frame_enabled', false);
	}

	public function testSubAlbumModeShowsSensitiveSubAlbumsWhenNotHidden(): void
	{
		Configs::set('mod_frame_include_subalbums', '1');
		Configs::set('hide_nsfw_in_frame', '0');
		$this->child->is_nsfw = true;
		$this->child->save();
		(new SyncRecursiveNsfw())->do();

		$response = $this->actingAs($this->userMayUpload1)->getJsonWithData('Frame', ['album_id' => $this->parent->id]);
		$this->assertOk($response);
		$response->assertJsonPath('photo.albums', ['Iceland']);

		$response = $this->actingAs($this->userMayUpload1)->getJsonWithData('Album::head', ['album_id' => $this->parent->id]);
		$this->assertOk($response);
		$response->assertJsonPath('config.is_mod_frame_enabled', true);
	}

	public function testSubAlbumModeKeepsTheSensitiveFramedAlbumItself(): void
	{
		Configs::set('mod_frame_include_subalbums', '1');
		Configs::set('hide_nsfw_in_frame', '1');
		$this->child->is_nsfw = true;
		$this->child->save();
		(new SyncRecursiveNsfw())->do();

		// The viewer chose the sensitive album: only sensitive albums below it are skipped.
		$response = $this->actingAs($this->userMayUpload1)->getJsonWithData('Frame', ['album_id' => $this->child->id]);
		$this->assertOk($response);
		$response->assertJsonPath('photo.albums', ['Iceland']);
	}
}
