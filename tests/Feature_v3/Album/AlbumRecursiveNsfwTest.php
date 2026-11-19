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

namespace Tests\Feature_v3\Album;

use App\Jobs\ApplyNsfwAlbumSensitivityJob;
use App\Models\Album;
use Illuminate\Support\Facades\DB;
use Tests\Feature_v3\Base\BaseApiWithDataTest;

/**
 * Feature 103 (FR-103-11): every writer of `is_nsfw` or of the album tree
 * keeps `albums.is_recursive_nsfw` equal to "the album or an ancestor is
 * NSFW" (S-103-23 … 26).
 */
class AlbumRecursiveNsfwTest extends BaseApiWithDataTest
{
	private function flagOf(string $album_id): bool
	{
		return (bool) DB::table('albums')->where('id', '=', $album_id)->value('is_recursive_nsfw');
	}

	/**
	 * Ids of the albums whose stored flag differs from the ancestry rule,
	 * computed here from `base_albums.is_nsfw` and the nested set.
	 *
	 * @return string[]
	 */
	private static function staleFlags(): array
	{
		$rows = DB::table('albums')
			->join('base_albums', 'base_albums.id', '=', 'albums.id')
			->select(['albums.id', 'albums._lft', 'albums._rgt', 'base_albums.is_nsfw', 'albums.is_recursive_nsfw'])
			->get();
		$nsfw = $rows->filter(fn (object $row) => (bool) $row->is_nsfw);

		return $rows
			->filter(fn (object $row) => (bool) $row->is_recursive_nsfw !== $nsfw->contains(fn (object $n) => $n->_lft <= $row->_lft && $n->_rgt >= $row->_rgt))
			->pluck('id')
			->values()
			->all();
	}

	private function assertFlagsMatchTree(): void
	{
		self::assertSame([], self::staleFlags(), 'Albums with a stale recursive NSFW flag.');
	}

	private function setNsfw(Album $album, bool $is_nsfw): void
	{
		$this->assertCreated($this->actingAs($this->admin)->postJson('Album::updateProtectionPolicy', [
			'album_id' => $album->id,
			'is_public' => false,
			'is_link_required' => false,
			'is_nsfw' => $is_nsfw,
			'grants_download' => false,
			'grants_upload' => false,
			'grants_full_photo_access' => false,
		]));
	}

	private function createAlbum(?Album $parent): string
	{
		$response = $this->actingAs($this->admin)->postJson('Album', [
			'parent_id' => $parent?->id,
			'title' => 'recursive_nsfw',
		]);
		$this->assertOk($response);

		return $response->getOriginalContent();
	}

	private function move(?Album $target, Album $album): void
	{
		$this->assertNoContent($this->actingAs($this->admin)->postJson('Album::move', [
			'album_id' => $target?->id,
			'album_ids' => [$album->id],
		]));
	}

	private function staleAllFlags(bool $value): void
	{
		DB::table('albums')->update(['is_recursive_nsfw' => $value]);
	}

	// ── Creation (S-103-23) ─────────────────────────────────────────

	public function testNewAlbumIsFlaggedBelowAnNsfwAlbum(): void
	{
		$this->setNsfw($this->album1, true);

		self::assertFalse($this->flagOf($this->createAlbum(null)));
		self::assertFalse($this->flagOf($this->createAlbum($this->album2)));
		self::assertTrue($this->flagOf($this->createAlbum($this->album1)));
		self::assertTrue($this->flagOf($this->createAlbum($this->subAlbum1)));
		$this->assertFlagsMatchTree();
	}

	public function testNewAlbumIsFlaggedFromTheTreeNotFromItsParentFlag(): void
	{
		DB::table('base_albums')->where('id', '=', $this->album1->id)->update(['is_nsfw' => true]);
		$this->staleAllFlags(false);

		self::assertTrue($this->flagOf($this->createAlbum($this->subAlbum1)));
	}

	// ── NSFW toggles (S-103-24) ─────────────────────────────────────

	public function testProtectionPolicyFlagsAndClearsTheSubtree(): void
	{
		$this->setNsfw($this->album1, true);
		self::assertTrue($this->flagOf($this->album1->id));
		self::assertTrue($this->flagOf($this->subAlbum1->id));
		$this->assertFlagsMatchTree();

		$this->setNsfw($this->album1, false);
		self::assertFalse($this->flagOf($this->album1->id));
		self::assertFalse($this->flagOf($this->subAlbum1->id));
		$this->assertFlagsMatchTree();
	}

	public function testClearingKeepsSubAlbumsBelowAnotherNsfwAlbum(): void
	{
		$this->setNsfw($this->subAlbum1, true);
		$this->setNsfw($this->album1, true);
		$this->setNsfw($this->album1, false);

		self::assertFalse($this->flagOf($this->album1->id));
		self::assertTrue($this->flagOf($this->subAlbum1->id));
		$this->assertFlagsMatchTree();
	}

	public function testBulkEditFlagsTheSubtree(): void
	{
		$this->assertNoContent($this->actingAs($this->admin)->patchJson('BulkAlbumEdit', [
			'album_ids' => [$this->album1->id],
			'is_nsfw' => true,
		]));

		self::assertTrue($this->flagOf($this->subAlbum1->id));
		$this->assertFlagsMatchTree();
	}

	public function testNsfwAlbumJobFlagsTheSubtree(): void
	{
		(new ApplyNsfwAlbumSensitivityJob([$this->album1->id]))->handle();

		self::assertTrue($this->flagOf($this->subAlbum1->id));
		$this->assertFlagsMatchTree();
	}

	// ── Tree changes (S-103-25) ─────────────────────────────────────

	public function testMoveFollowsTheNewAncestry(): void
	{
		$this->setNsfw($this->album2, true);

		$this->move($this->album2, $this->album1);
		self::assertTrue($this->flagOf($this->album1->id));
		self::assertTrue($this->flagOf($this->subAlbum1->id));
		$this->assertFlagsMatchTree();

		$this->move(null, $this->album1);
		self::assertFalse($this->flagOf($this->album1->id));
		self::assertFalse($this->flagOf($this->subAlbum1->id));
		$this->assertFlagsMatchTree();
	}

	public function testMergeFlagsTheMovedSubAlbums(): void
	{
		$this->setNsfw($this->album2, true);

		$this->assertNoContent($this->actingAs($this->admin)->postJson('Album::merge', [
			'album_id' => $this->album2->id,
			'album_ids' => [$this->album1->id],
		]));

		self::assertTrue($this->flagOf($this->subAlbum1->id));
		$this->assertFlagsMatchTree();
	}

	public function testTransferMakesTheAlbumARootAndClearsItsFlag(): void
	{
		$this->setNsfw($this->album1, true);

		$this->assertNoContent($this->actingAs($this->admin)->postJson('Album::transfer', [
			'album_id' => $this->subAlbum1->id,
			'user_id' => $this->userMayUpload2->id,
		]));

		self::assertFalse($this->flagOf($this->subAlbum1->id));
		$this->assertFlagsMatchTree();
	}

	// ── Tree repairs (S-103-26) ─────────────────────────────────────

	public function testFixTreeRecomputesEveryFlag(): void
	{
		$this->setNsfw($this->album1, true);
		$this->staleAllFlags(false);

		$this->artisan('lychee:fix-tree')->assertSuccessful();

		self::assertTrue($this->flagOf($this->subAlbum1->id));
		$this->assertFlagsMatchTree();
	}

	public function testFullTreeUpdateRecomputesEveryFlag(): void
	{
		$this->setNsfw($this->album1, true);
		$this->staleAllFlags(true);
		$albums = DB::table('albums')->select(['id', '_lft', '_rgt', 'parent_id'])->get()
			->map(fn (object $row) => ['id' => $row->id, '_lft' => $row->_lft, '_rgt' => $row->_rgt, 'parent_id' => $row->parent_id])
			->all();

		$this->assertNoContent($this->actingAs($this->admin)->postJson('Maintenance::fullTree', ['albums' => $albums]));

		self::assertFalse($this->flagOf($this->album2->id));
		$this->assertFlagsMatchTree();
	}
}
