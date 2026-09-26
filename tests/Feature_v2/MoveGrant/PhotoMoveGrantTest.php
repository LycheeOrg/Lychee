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

namespace Tests\Feature_v2\MoveGrant;

use App\Constants\PhotoAlbum as PA;
use App\Models\AccessPermission;
use App\Models\Album;
use App\Models\Configs;
use App\Models\Photo;
use Illuminate\Support\Facades\DB;
use Tests\Feature_v2\Base\BaseApiWithDataTest;

/**
 * `Photo::copy` / `Photo::move` authorization.
 *
 * P = photo1, owned by userMayUpload1 (V), in album1.
 */
class PhotoMoveGrantTest extends BaseApiWithDataTest
{
	use MoveGrantFixture;

	private Album $victim_other;

	public function setUp(): void
	{
		parent::setUp();
		$this->createMoveGrantFixture();
		$this->victim_other = Album::factory()->as_root()->owned_by($this->userMayUpload1)->create();
	}

	public function tearDown(): void
	{
		Configs::set('secure_image_link_enabled', '0');
		parent::tearDown();
	}

	/**
	 * @return string[]
	 */
	private function albumsOf(Photo $photo): array
	{
		return DB::table(PA::PHOTO_ALBUM)->where(PA::PHOTO_ID, '=', $photo->id)->orderBy(PA::ALBUM_ID)->pluck(PA::ALBUM_ID)->all();
	}

	private function copy(array $photo_ids, ?Album $target): \Illuminate\Testing\TestResponse
	{
		return $this->actingAs($this->attacker)->postJson('Photo::copy', [
			'photo_ids' => $photo_ids,
			'album_id' => $target?->id,
		]);
	}

	private function move(array $photo_ids, Album $from, ?Album $target): \Illuminate\Testing\TestResponse
	{
		return $this->actingAs($this->attacker)->postJson('Photo::move', [
			'photo_ids' => $photo_ids,
			'album_id' => $target?->id,
			'from_id' => $from->id,
		]);
	}

	/** Edit alone does not allow copying into one's own album. */
	public function testEditOnlyCannotCopyVictimPhotoIntoOwnAlbum(): void
	{
		$this->grant($this->album1, ['edit']);

		$this->assertForbidden($this->copy([$this->photo1->id], $this->attacker_album));
		self::assertSame([$this->album1->id], $this->albumsOf($this->photo1));
	}

	/** Same, with encrypted image links. */
	public function testEditOnlyCannotCopyWithSecureImageLinks(): void
	{
		Configs::set('secure_image_link_enabled', '1');
		$this->grant($this->album1, ['edit']);

		$this->assertForbidden($this->copy([$this->photo1->id], $this->attacker_album));
		self::assertSame([$this->album1->id], $this->albumsOf($this->photo1));
	}

	public function testEditOnlyCannotMoveVictimPhotoIntoOwnAlbum(): void
	{
		$this->grant($this->album1, ['edit']);

		$this->assertForbidden($this->move([$this->photo1->id], $this->album1, $this->attacker_album));
		self::assertSame([$this->album1->id], $this->albumsOf($this->photo1));
	}

	/** Edit alone no longer reorganises. */
	public function testEditOnlyCannotMoveBetweenVictimAlbums(): void
	{
		$this->grant($this->album1, ['edit']);
		$this->grant($this->victim_other, ['edit']);

		$this->assertForbidden($this->move([$this->photo1->id], $this->album1, $this->victim_other));
	}

	public function testMoveGrantCanMoveBetweenVictimAlbums(): void
	{
		$this->grant($this->album1, ['move']);
		$this->grant($this->victim_other, ['edit']);

		$this->assertNoContent($this->move([$this->photo1->id], $this->album1, $this->victim_other));
		self::assertSame([$this->victim_other->id], $this->albumsOf($this->photo1));
	}

	/** Cross-owner without full access + download. */
	public function testMoveGrantCannotCopyIntoOwnAlbum(): void
	{
		$this->grant($this->album1, ['move']);

		$this->assertForbidden($this->copy([$this->photo1->id], $this->attacker_album));
		$this->assertForbidden($this->move([$this->photo1->id], $this->album1, $this->attacker_album));
		self::assertSame([$this->album1->id], $this->albumsOf($this->photo1));
	}

	/** Full access alone is not enough; download is required too. */
	public function testMoveAndFullWithoutDownloadCannotCopyIntoOwnAlbum(): void
	{
		$this->grant($this->album1, ['move', 'full']);

		$this->assertForbidden($this->copy([$this->photo1->id], $this->attacker_album));
	}

	/** The user already holds full access + download: nothing new is gained. */
	public function testMoveFullAndDownloadCanCopyIntoOwnAlbum(): void
	{
		$this->grant($this->album1, ['move', 'full', 'download']);

		$this->assertNoContent($this->copy([$this->photo1->id], $this->attacker_album));
		self::assertEqualsCanonicalizing([$this->album1->id, $this->attacker_album->id], $this->albumsOf($this->photo1));
	}

	/** Batch is all-or-nothing. */
	public function testBatchWithOneForbiddenPhotoCopiesNothing(): void
	{
		$own_album = Album::factory()->as_root()->owned_by($this->attacker)->create();
		$own_photo = Photo::factory()->owned_by($this->attacker)->in($own_album)->create();
		$this->grant($this->album1, ['move']);

		$this->assertForbidden($this->copy([$own_photo->id, $this->photo1->id], $this->attacker_album));
		self::assertSame([$own_album->id], $this->albumsOf($own_photo));
	}

	/** Accepted residual path: same owner, target shared publicly with full access. */
	public function testMoveGrantCanMoveIntoOwnersPublicAlbum(): void
	{
		AccessPermission::factory()->public()->visible()->grants_full_photo()->for_album($this->victim_other)->create();
		$this->grant($this->album1, ['move']);
		$this->grant($this->victim_other, ['edit']);

		$this->assertNoContent($this->move([$this->photo1->id], $this->album1, $this->victim_other));
	}

	/** A collaborator's own upload in the victim's album. */
	public function testOwnPhotoInVictimAlbumCanBeCopiedIntoOwnAlbum(): void
	{
		$own_photo = Photo::factory()->owned_by($this->attacker)->in($this->album1)->create();
		$this->grant($this->album1, ['edit']);

		$this->assertNoContent($this->copy([$own_photo->id], $this->attacker_album));
	}

	/** Move to root lands in the photo owner's unsorted. */
	public function testMoveGrantCanMoveToRoot(): void
	{
		$this->grant($this->album1, ['move']);

		$this->assertNoContent($this->move([$this->photo1->id], $this->album1, null));
		self::assertSame([], $this->albumsOf($this->photo1));
		self::assertSame($this->userMayUpload1->id, Photo::query()->findOrFail($this->photo1->id)->owner_id);
	}

	/** Admin bypass. */
	public function testAdminCanCopyAcrossOwners(): void
	{
		$response = $this->actingAs($this->admin)->postJson('Photo::copy', [
			'photo_ids' => [$this->photo1->id],
			'album_id' => $this->attacker_album->id,
		]);
		$this->assertNoContent($response);
	}
}
