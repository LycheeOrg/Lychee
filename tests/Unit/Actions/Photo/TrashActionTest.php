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

namespace Tests\Unit\Actions\Photo;

use App\Actions\Photo\Trash;
use App\Constants\QueryLimits;
use App\Enum\SmartAlbumType;
use App\Models\Album;
use App\Models\Photo;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\AbstractTestCase;
use Tests\Traits\CreatesBulkFixtures;

/**
 * Feature 119 (FR-119-01/02, S-119-02..05): a photo enters the trash only
 * when its last album link is removed.
 */
class TrashActionTest extends AbstractTestCase
{
	use DatabaseTransactions;
	use CreatesBulkFixtures;

	private User $owner;

	public function setUp(): void
	{
		parent::setUp();
		$this->owner = User::factory()->create();
	}

	/**
	 * S-119-02: a photo also in a surviving album keeps that link and is not
	 * trashed; a photo only in the deleted album is.
	 */
	public function testFromAlbumsKeepsPhotosLinkedToASurvivingAlbum(): void
	{
		$deleted = $this->album();
		$surviving = $this->album();
		$shared = $this->photoIn($deleted, $surviving);
		$alone = $this->photoIn($deleted);

		$trashed = (new Trash())->fromAlbums([$deleted->id]);

		self::assertSame([$alone->id], $trashed);
		self::assertSame([$surviving->id], $this->linksOf($shared));
		self::assertSame([], $this->linksOf($alone));
		self::assertTrue($this->isTrashed($alone));
		self::assertFalse($this->isTrashed($shared));
	}

	/**
	 * S-119-03: a photo in two deleted albums that fall in different album
	 * chunks is trashed once.
	 */
	public function testFromAlbumsTrashesAPhotoInSeveralDeletedAlbumsOnce(): void
	{
		$parent = $this->album();
		$album_ids = $this->bulkChildAlbums($parent, $this->owner, QueryLimits::ID_CHUNK_SIZE + 1);
		$photo = Photo::factory()->owned_by($this->owner)->create();
		$photo->albums()->attach([$album_ids[0], $album_ids[count($album_ids) - 1]]);

		$trashed = (new Trash())->fromAlbums($album_ids);

		self::assertSame([$photo->id], $trashed);
		self::assertSame(1, DB::table('trashed_photos')->where('photo_id', '=', $photo->id)->count());
	}

	/**
	 * S-119-04: deleting from album X removes the links to X only; photos
	 * left without a link are trashed, and so are photos deleted from Unsorted.
	 */
	public function testFromAlbumRemovesTheLinksToThatAlbumOnly(): void
	{
		$x = $this->album();
		$y = $this->album();
		$in_both = $this->photoIn($x, $y);
		$only_in_x = $this->photoIn($x);

		$trashed = (new Trash())->fromAlbum([$in_both->id, $only_in_x->id], $x->id);

		self::assertSame([$only_in_x->id], $trashed);
		self::assertSame([$y->id], $this->linksOf($in_both));
		self::assertFalse($this->isTrashed($in_both));
		self::assertTrue($this->isTrashed($only_in_x));
	}

	public function testFromUnsortedTrashesPhotosWithoutAlbum(): void
	{
		$unsorted = Photo::factory()->owned_by($this->owner)->create();

		$trashed = (new Trash())->fromAlbum([$unsorted->id], SmartAlbumType::UNSORTED->value);

		self::assertSame([$unsorted->id], $trashed);
		self::assertTrue($this->isTrashed($unsorted));
	}

	/**
	 * S-119-05: everywhere() removes every link, then trashes the photo.
	 */
	public function testEverywhereRemovesEveryLink(): void
	{
		$photo = $this->photoIn($this->album(), $this->album());

		$trashed = (new Trash())->everywhere([$photo->id]);

		self::assertSame([$photo->id], $trashed);
		self::assertSame([], $this->linksOf($photo));
		self::assertTrue($this->isTrashed($photo));
	}

	public function testAPhotoAlreadyTrashedIsNotTrashedTwice(): void
	{
		$photo = $this->photoIn($this->album());
		(new Trash())->everywhere([$photo->id]);

		$trashed = (new Trash())->everywhere([$photo->id]);

		self::assertSame([], $trashed);
		self::assertSame(1, DB::table('trashed_photos')->where('photo_id', '=', $photo->id)->count());
	}

	/**
	 * FR-119-06: covers and headers pointing to a trashed photo are reset
	 * when it is trashed, also in albums that are not deleted.
	 */
	public function testTrashingResetsCoversAndHeaders(): void
	{
		$album = $this->album();
		$photo = $this->photoIn($album);
		$other = $this->album();
		DB::table('albums')->where('id', '=', $other->id)->update(['cover_id' => $photo->id, 'header_id' => $photo->id]);

		(new Trash())->fromAlbum([$photo->id], $album->id);

		$row = DB::table('albums')->where('id', '=', $other->id)->first();
		self::assertNull($row->cover_id);
		self::assertNull($row->header_id);
	}

	private function album(): Album
	{
		return Album::factory()->as_root()->owned_by($this->owner)->create();
	}

	private function photoIn(Album ...$albums): Photo
	{
		$photo = Photo::factory()->owned_by($this->owner)->create();
		$photo->albums()->attach(array_map(fn (Album $album) => $album->id, $albums));

		return $photo;
	}

	/**
	 * @return string[]
	 */
	private function linksOf(Photo $photo): array
	{
		return DB::table('photo_album')->where('photo_id', '=', $photo->id)->orderBy('album_id')->pluck('album_id')->all();
	}

	private function isTrashed(Photo $photo): bool
	{
		return DB::table('trashed_photos')->where('photo_id', '=', $photo->id)->exists();
	}
}
