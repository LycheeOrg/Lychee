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

namespace Tests\Feature_v2\Sitemap;

use App\Actions\Album\SyncRecursiveNsfw;
use App\Actions\Sitemap\ListSitemapAlbums;
use App\Events\AlbumSaved;
use App\Models\AccessPermission;
use App\Models\Album;
use Illuminate\Support\Facades\Log;
use Tests\Feature_v2\Base\BaseApiWithDataTest;

/**
 * Feature 117: albums listed in the sitemap (FR-117-02 … FR-117-04, FR-117-08,
 * FR-117-10, FR-117-11). Fixtures: `album4` and its child `subAlbum4` are
 * public and visible; every other fixture album is private.
 */
class SitemapAlbumsTest extends BaseApiWithDataTest
{
	/**
	 * S-117-02, S-117-03: public albums and their public children are listed
	 * in tree order, private albums are not.
	 */
	public function testPublicAlbumsInTreeOrder(): void
	{
		$rows = $this->list();

		self::assertSame([$this->album4->id, $this->subAlbum4->id], array_column($rows, 'id'));
		self::assertSame(['id', 'slug', 'updated_at', 'max_taken_at'], array_keys($rows[0]));
	}

	/**
	 * S-117-04: a password-protected public album and its public child are left out.
	 */
	public function testPasswordProtectedAlbumIsLeftOut(): void
	{
		$locked = $this->publicRoot();
		AccessPermission::factory()->public()->visible()->locked()->for_album($locked)->create();
		$child = $this->publicChildOf($locked);

		$ids = array_column($this->list(), 'id');

		self::assertContains($this->album4->id, $ids);
		self::assertNotContains($locked->id, $ids);
		self::assertNotContains($child->id, $ids);
	}

	/**
	 * S-117-05: a public album that requires a direct link and its public child are left out.
	 */
	public function testLinkRequiredAlbumIsLeftOut(): void
	{
		$hidden = $this->publicRoot();
		AccessPermission::factory()->public()->for_album($hidden)->create();
		$child = $this->publicChildOf($hidden);

		$ids = array_column($this->list(), 'id');

		self::assertContains($this->album4->id, $ids);
		self::assertNotContains($hidden->id, $ids);
		self::assertNotContains($child->id, $ids);
	}

	/**
	 * S-117-06: a public album inside a private album is left out.
	 */
	public function testPublicAlbumInsidePrivateAlbumIsLeftOut(): void
	{
		$child = $this->publicChildOf($this->album1);

		$ids = array_column($this->list(), 'id');

		self::assertContains($this->album4->id, $ids);
		self::assertNotContains($child->id, $ids);
	}

	/**
	 * S-117-09: with root only, nested albums are left out.
	 */
	public function testRootOnly(): void
	{
		self::assertSame([$this->album4->id], array_column($this->list(root_only: true), 'id'));
	}

	/**
	 * S-117-14: a sensitive public album and its public child are left out
	 * while hide_nsfw is on, and listed while it is off.
	 */
	public function testSensitiveAlbums(): void
	{
		$sensitive = $this->publicRoot();
		AccessPermission::factory()->public()->visible()->for_album($sensitive)->create();
		$child = $this->publicChildOf($sensitive);
		$sensitive->is_nsfw = true;
		$sensitive->save();
		resolve(SyncRecursiveNsfw::class)->do();

		$hidden = array_column($this->list(hide_nsfw: true), 'id');
		self::assertNotContains($sensitive->id, $hidden);
		self::assertNotContains($child->id, $hidden);
		self::assertContains($this->album4->id, $hidden);

		$shown = array_column($this->list(hide_nsfw: false), 'id');
		self::assertContains($sensitive->id, $shown);
		self::assertContains($child->id, $shown);
	}

	/**
	 * S-117-15: a public tag album is never listed.
	 */
	public function testPublicTagAlbumIsLeftOut(): void
	{
		AccessPermission::factory()->public()->visible()->create(['base_album_id' => $this->tagAlbum1->id]);

		$ids = array_column($this->list(), 'id');

		self::assertContains($this->album4->id, $ids);
		self::assertNotContains($this->tagAlbum1->id, $ids);
	}

	/**
	 * FR-117-06: the slug is carried along for the URL.
	 */
	public function testSlugIsReturned(): void
	{
		$this->album4->slug = 'public-album';
		$this->album4->save();

		$rows = $this->list();

		self::assertSame('public-album', $rows[0]['slug']);
		self::assertNull($rows[1]['slug']);
	}

	/**
	 * S-117-20: the list stops at the cap, in tree order, and a warning is logged.
	 */
	public function testCap(): void
	{
		Log::spy();
		$action = app()->make(ListSitemapAlbums::class, ['max_albums' => 1]);

		$rows = $action->do(false, true);

		self::assertSame([$this->album4->id], array_column($rows, 'id'));
		Log::shouldHaveReceived('warning')->once();
	}

	public function testNoWarningBelowTheCap(): void
	{
		Log::spy();

		$this->list();

		Log::shouldNotHaveReceived('warning');
	}

	/**
	 * NFR-117-02, S-117-19: the rows are served from the managed cache until
	 * an album event reaches the album-listing invalidator.
	 */
	public function testRowsAreCachedUntilAnAlbumChanges(): void
	{
		config(['features.enable-caching' => true]);
		$before = array_column($this->list(), 'id');

		$new = $this->publicRoot();
		AccessPermission::factory()->public()->visible()->for_album($new)->create();
		self::assertSame($before, array_column($this->list(), 'id'), 'expected the cached rows');

		AlbumSaved::dispatch([$new->id], [null]);
		self::assertContains($new->id, array_column($this->list(), 'id'));
	}

	/**
	 * @return list<array{id:string,slug:string|null,updated_at:string|null,max_taken_at:string|null}>
	 */
	private function list(bool $root_only = false, bool $hide_nsfw = true): array
	{
		return resolve(ListSitemapAlbums::class)->do($root_only, $hide_nsfw);
	}

	private function publicRoot(): Album
	{
		return Album::factory()->as_root()->owned_by($this->admin)->create();
	}

	private function publicChildOf(Album $parent): Album
	{
		$child = Album::factory()->children_of($parent)->owned_by($this->admin)->create();
		AccessPermission::factory()->public()->visible()->for_album($child)->create();

		return $child;
	}
}
