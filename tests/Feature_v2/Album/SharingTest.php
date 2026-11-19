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

use App\Constants\AccessPermissionConstants as APC;
use App\Events\AccessPermissionChanged;
use App\Events\AlbumListingCacheFlushRequested;
use App\Models\AccessPermission;
use App\Models\AlbumUserThumb;
use Illuminate\Support\Facades\Event;
use Tests\Feature_v2\Base\BaseApiWithDataTest;

class SharingTest extends BaseApiWithDataTest
{
	public function testCreateDispatchesAccessPermissionChangedPerAlbum(): void
	{
		Event::fake([AccessPermissionChanged::class]);

		$response = $this->actingAs($this->userMayUpload2)->postJson('Sharing', [
			'user_ids' => [$this->userMayUpload1->id],
			'group_ids' => [],
			'album_ids' => [$this->album2->id],
			'grants_edit' => true,
			'grants_move' => true,
			'grants_delete' => true,
			'grants_download' => true,
			'grants_full_photo_access' => true,
			'grants_upload' => true,
		]);
		$this->assertOk($response);

		Event::assertDispatched(AccessPermissionChanged::class, fn (AccessPermissionChanged $e) => $e->base_album_id === $this->album2->id);
	}

	public function testEditDispatchesAccessPermissionChanged(): void
	{
		Event::fake([AccessPermissionChanged::class]);

		$response = $this->actingAs($this->userMayUpload1)->patchJson('Sharing', [
			'perm_id' => $this->perm1->id,
			'grants_edit' => true,
			'grants_delete' => true,
			'grants_download' => true,
			'grants_full_photo_access' => true,
			'grants_upload' => true,
			'grants_move' => true,
		]);
		$this->assertOk($response);

		Event::assertDispatched(AccessPermissionChanged::class, fn (AccessPermissionChanged $e) => $e->base_album_id === $this->album1->id);
	}

	public function testDeleteDispatchesAccessPermissionChanged(): void
	{
		Event::fake([AccessPermissionChanged::class]);

		$response = $this->actingAs($this->userMayUpload1)->deleteJson('Sharing', ['perm_id' => $this->perm1->id]);
		$this->assertNoContent($response);

		Event::assertDispatched(AccessPermissionChanged::class, fn (AccessPermissionChanged $e) => $e->base_album_id === $this->album1->id);
	}

	/**
	 * Revoking the *public* permission of a source album must also drop the
	 * cached tag/person/smart-album covers which *authenticated* viewers
	 * materialised while that permission was in place.
	 *
	 * A row in `album_user_thumbs` is keyed by the viewer who materialised it
	 * (`Auth::id()`), never by the permission their access came from, so a
	 * logged-in viewer reaching the album through its public permission still
	 * stores the row under their own `user_id`. Purging only `user_id IS NULL`
	 * left those rows behind, and GetPhotoAssetRequest treats such a row as
	 * proof that the photo legitimately represents the album - so the revoked
	 * viewer kept being served the private thumbnail.
	 */
	public function testDeletePublicPermissionPurgesAuthenticatedViewersCachedThumbs(): void
	{
		$public_perm = AccessPermission::factory()->public()->visible()->for_album($this->album1)->create();

		AlbumUserThumb::query()->create([
			'user_id' => $this->userNoUpload->id,
			'album_id' => $this->tagAlbum1->id,
			'photo_id' => $this->photo1->id,
		]);

		$response = $this->actingAs($this->userMayUpload1)->deleteJson('Sharing', ['perm_id' => $public_perm->id]);
		$this->assertNoContent($response);

		self::assertSame(0, AlbumUserThumb::query()->where('photo_id', '=', $this->photo1->id)->count());
	}

	/**
	 * Same for a group permission: the rows are keyed by the individual
	 * members' `user_id`, but a member may equally have reached the album
	 * through an unrelated permission, so the purge is unconditional.
	 */
	public function testDeleteGroupPermissionPurgesEveryViewersCachedThumbs(): void
	{
		AlbumUserThumb::query()->create([
			'user_id' => $this->userNoUpload->id,
			'album_id' => $this->tagAlbum1->id,
			'photo_id' => $this->photo1->id,
		]);

		$response = $this->actingAs($this->userMayUpload1)->deleteJson('Sharing', ['perm_id' => $this->perm11->id]);
		$this->assertNoContent($response);

		self::assertSame(0, AlbumUserThumb::query()->where('photo_id', '=', $this->photo1->id)->count());
	}

	/**
	 * The purge stays scoped to the album whose permission was revoked:
	 * a cached cover pointing at a photo of an *unrelated* album survives.
	 */
	public function testDeletePermissionLeavesUnrelatedAlbumsCachedThumbsAlone(): void
	{
		AlbumUserThumb::query()->create([
			'user_id' => $this->userNoUpload->id,
			'album_id' => $this->tagAlbum1->id,
			'photo_id' => $this->photo2->id,
		]);

		$response = $this->actingAs($this->userMayUpload1)->deleteJson('Sharing', ['perm_id' => $this->perm1->id]);
		$this->assertNoContent($response);

		self::assertSame(1, AlbumUserThumb::query()->where('photo_id', '=', $this->photo2->id)->count());
	}

	public function testPropagateUpdateDispatchesCoarseFlush(): void
	{
		Event::fake([AlbumListingCacheFlushRequested::class]);

		$response = $this->actingAs($this->userMayUpload1)->putJson('Sharing', [
			'album_id' => $this->album1->id,
			'shall_override' => false,
		]);
		$this->assertNoContent($response);

		Event::assertDispatched(AlbumListingCacheFlushRequested::class);
	}

	public function testPropagateOverwriteDispatchesCoarseFlush(): void
	{
		Event::fake([AlbumListingCacheFlushRequested::class]);

		$response = $this->actingAs($this->userMayUpload1)->putJson('Sharing', [
			'album_id' => $this->album1->id,
			'shall_override' => true,
		]);
		$this->assertNoContent($response);

		Event::assertDispatched(AlbumListingCacheFlushRequested::class);
	}

	/**
	 * `Propagate::overwrite()` wipes every own permission the descendants had
	 * before re-inserting the ancestor's — a revocation for anyone whose access
	 * came from a descendant-only permission, so the covers those permissions
	 * produced go with them.
	 */
	public function testPropagateOverwritePurgesDescendantsCachedThumbs(): void
	{
		AlbumUserThumb::query()->create([
			'user_id' => $this->userNoUpload->id,
			'album_id' => $this->tagAlbum1->id,
			'photo_id' => $this->subPhoto1->id,
		]);

		$response = $this->actingAs($this->userMayUpload1)->putJson('Sharing', [
			'album_id' => $this->album1->id,
			'shall_override' => true,
		]);
		$this->assertNoContent($response);

		self::assertSame(0, AlbumUserThumb::query()->where('photo_id', '=', $this->subPhoto1->id)->count());
	}

	public function testGet(): void
	{
		$response = $this->getJsonWithData('Sharing');
		$this->assertUnprocessable($response);

		$response = $this->getJsonWithData('Sharing', ['album_id' => $this->album1->id]);
		$this->assertUnauthorized($response);

		$response = $this->getJsonWithData('Sharing::albums');
		$this->assertUnauthorized($response);
	}

	public function testUserForbidden(): void
	{
		$response = $this->actingAs($this->userNoUpload)->getJsonWithData('Sharing');
		$this->assertUnprocessable($response);

		$response = $this->actingAs($this->userNoUpload)->getJsonWithData('Sharing::all');
		$this->assertForbidden($response);

		$response = $this->actingAs($this->userNoUpload)->getJsonWithData('Sharing::albums');
		$this->assertForbidden($response);
	}

	public function testUserGet(): void
	{
		$response = $this->actingAs($this->userMayUpload1)->getJsonWithData('Sharing');
		$this->assertUnprocessable($response);

		$response = $this->actingAs($this->userMayUpload1)->getJsonWithData('Sharing', ['album_id' => $this->album1->id]);
		$this->assertOk($response);

		$response = $this->actingAs($this->userMayUpload1)->getJsonWithData('Sharing::all');
		$this->assertOk($response);

		$response = $this->actingAs($this->userMayUpload1)->getJsonWithData('Sharing::albums');
		$this->assertOk($response);
		$response->assertJson([
			[
				'id' => $this->album1->id,
				'title' => $this->album1->title,
				'original' => $this->album1->title,
				'short_title' => $this->album1->title,
			],
			[
				'id' => $this->subAlbum1->id,
				'title' => $this->album1->title . '/' . $this->subAlbum1->title,
				'original' => $this->subAlbum1->title,
				'short_title' => $this->album1->title . '/' . $this->subAlbum1->title,
			],
		]);

		$response = $this->actingAs($this->userMayUpload1)->patchJson('Sharing', [
			'perm_id' => $this->perm1->id,
			'grants_edit' => true,
			'grants_delete' => true,
			'grants_download' => true,
			'grants_full_photo_access' => true,
			'grants_upload' => true,
			'grants_move' => true,
		]);
		$this->assertOk($response);

		$response = $this->actingAs($this->userMayUpload2)->postJson('Sharing', [
			'user_ids' => [$this->userMayUpload1->id],
			'group_ids' => [],
			'album_ids' => [$this->album2->id],
			'grants_edit' => true,
			'grants_move' => true,
			'grants_delete' => true,
			'grants_download' => true,
			'grants_full_photo_access' => true,
			'grants_upload' => true,
		]);
		$this->assertOk($response);

		$response = $this->actingAs($this->userMayUpload2)->getJsonWithData('Sharing', ['album_id' => $this->album2->id]);
		$this->assertOk($response);
		$response->assertJsonCount(1);

		$id = $response->json()[0]['id'];
		$response = $this->actingAs($this->userMayUpload1)->deleteJson('Sharing', ['perm_id' => $id]);
		$this->assertForbidden($response);

		$response = $this->actingAs($this->userMayUpload2)->deleteJson('Sharing', ['perm_id' => $id]);
		$this->assertNoContent($response);
	}

	public function testUpdateOverrideForbidden(): void
	{
		$response = $this->putJson('Sharing', []);
		$this->assertUnprocessable($response);

		$response = $this->putJson('Sharing', [
			'album_id' => $this->album1->id,
			'shall_override' => true,
		]);
		$this->assertUnauthorized($response);

		$response = $this->actingAs($this->userMayUpload2)->putJson('Sharing', []);
		$this->assertUnprocessable($response);

		$response = $this->actingAs($this->userMayUpload2)->putJson('Sharing', [
			'album_id' => $this->album1->id,
			'shall_override' => true,
		]);
		$this->assertForbidden($response);
	}

	public function testUpdate(): void
	{
		$response = $this->actingAs($this->userMayUpload1)->putJson('Sharing', []);
		$this->assertUnprocessable($response);

		// Update sub album permission.
		$response = $this->actingAs($this->userMayUpload1)->putJson('Sharing', [
			'album_id' => $this->album1->id,
			'shall_override' => false,
		]);
		$this->assertNoContent($response);
		self::assertEquals(2, AccessPermission::where(APC::BASE_ALBUM_ID, '=', $this->subAlbum1->id)->count());
		$perm = AccessPermission::where(APC::BASE_ALBUM_ID, '=', $this->subAlbum1->id)->whereNull(APC::USER_GROUP_ID)->first();

		// Update the permission with false
		$response = $this->actingAs($this->userMayUpload1)->patchJson('Sharing', [
			'perm_id' => $perm->id,
			'grants_edit' => false,
			'grants_delete' => false,
			'grants_download' => false,
			'grants_full_photo_access' => false,
			'grants_upload' => false,
			'grants_move' => false,
		]);
		$this->assertOk($response);

		// Verify the permission
		$perm = AccessPermission::where(APC::BASE_ALBUM_ID, '=', $this->subAlbum1->id)->whereNull(APC::USER_GROUP_ID)->first();
		self::assertFalse($perm->grants_edit);
		self::assertFalse($perm->grants_delete);
		self::assertFalse($perm->grants_download);
		self::assertFalse($perm->grants_full_photo_access);
		self::assertFalse($perm->grants_upload);

		// Apply update again
		$response = $this->actingAs($this->userMayUpload1)->putJson('Sharing', [
			'album_id' => $this->album1->id,
			'shall_override' => false,
		]);
		$this->assertNoContent($response);
		// Verify the count is still 2.
		self::assertEquals(2, AccessPermission::where(APC::BASE_ALBUM_ID, '=', $this->subAlbum1->id)->count());

		// Verify the permission
		$perm = AccessPermission::where(APC::BASE_ALBUM_ID, '=', $this->subAlbum1->id)->whereNull(APC::USER_GROUP_ID)->first();
		self::assertTrue($perm->grants_edit);
		self::assertTrue($perm->grants_delete);
		self::assertTrue($perm->grants_download);
		self::assertTrue($perm->grants_full_photo_access);
		self::assertTrue($perm->grants_upload);
	}

	public function testUpdateDoesNotLeakUnrelatedAlbumGroupPermissions(): void
	{
		// Give an *unrelated* album (not in album1's tree) a group permission.
		// A buggy query scoping in Propagate::applyUpdate would leak this into
		// album1's descendants because it is not actually restricted to album1.
		$response = $this->actingAs($this->userMayUpload2)->postJson('Sharing', [
			'user_ids' => [],
			'group_ids' => [$this->group2->id],
			'album_ids' => [$this->album2->id],
			'grants_edit' => true,
			'grants_move' => true,
			'grants_delete' => true,
			'grants_download' => true,
			'grants_full_photo_access' => true,
			'grants_upload' => true,
		]);
		$this->assertOk($response);

		// Propagate album1 (unrelated to album2) to its descendants.
		$response = $this->actingAs($this->userMayUpload1)->putJson('Sharing', [
			'album_id' => $this->album1->id,
			'shall_override' => false,
		]);
		$this->assertNoContent($response);

		// subAlbum1 should only inherit album1's own permissions (perm1, perm11),
		// not the unrelated group2 permission that lives on album2.
		self::assertEquals(2, AccessPermission::where(APC::BASE_ALBUM_ID, '=', $this->subAlbum1->id)->count());
		self::assertEquals(0,
			AccessPermission::query()
				->where(APC::BASE_ALBUM_ID, '=', $this->subAlbum1->id)
				->where(APC::USER_GROUP_ID, '=', $this->group2->id)
				->count());
	}

	public function testOverride(): void
	{
		// Set up the permission in subSlbum
		$response = $this->actingAs($this->userMayUpload1)->postJson('Sharing', [
			'user_ids' => [$this->userLocked->id],
			'group_ids' => [],
			'album_ids' => [$this->subAlbum1->id],
			'grants_edit' => true,
			'grants_move' => true,
			'grants_delete' => true,
			'grants_download' => true,
			'grants_full_photo_access' => true,
			'grants_upload' => true,
		]);
		$this->assertOk($response);
		self::assertEquals(1, AccessPermission::where(APC::BASE_ALBUM_ID, '=', $this->subAlbum1->id)->count());

		$response = $this->actingAs($this->userMayUpload1)->postJson('Sharing', [
			'user_ids' => [$this->userNoUpload->id],
			'group_ids' => [],
			'album_ids' => [$this->album1->id],
			'grants_edit' => true,
			'grants_move' => true,
			'grants_delete' => true,
			'grants_download' => true,
			'grants_full_photo_access' => true,
			'grants_upload' => true,
		]);
		$this->assertOk($response);
		self::assertEquals(3, AccessPermission::where(APC::BASE_ALBUM_ID, '=', $this->album1->id)->count());

		// Update sub album permission.
		$response = $this->actingAs($this->userMayUpload1)->putJson('Sharing', [
			'album_id' => $this->album1->id,
			'shall_override' => true,
		]);
		$this->assertNoContent($response);
		self::assertEquals(0,
			AccessPermission::query()
				->where(APC::BASE_ALBUM_ID, '=', $this->subAlbum1->id)
				->where(APC::USER_ID, '=', $this->userLocked->id)
				->count());
		self::assertEquals(1,
			AccessPermission::query()
				->where(APC::BASE_ALBUM_ID, '=', $this->subAlbum1->id)
				->where(APC::USER_ID, '=', $this->userMayUpload2->id)
				->count());
		self::assertEquals(1,
			AccessPermission::query()
				->where(APC::BASE_ALBUM_ID, '=', $this->subAlbum1->id)
				->where(APC::USER_ID, '=', $this->userNoUpload->id)
				->count());
	}

	public function testOverrideMixed(): void
	{
		// Set up the permission in subSlbum
		$response = $this->actingAs($this->userMayUpload1)->postJson('Sharing', [
			'user_ids' => [$this->userLocked->id, $this->userNoUpload->id],
			'group_ids' => [$this->group2->id],
			'album_ids' => [$this->subAlbum1->id],
			'grants_edit' => true,
			'grants_move' => true,
			'grants_delete' => true,
			'grants_download' => true,
			'grants_full_photo_access' => true,
			'grants_upload' => true,
		]);
		$this->assertOk($response);
		self::assertEquals(3, AccessPermission::where(APC::BASE_ALBUM_ID, '=', $this->subAlbum1->id)->count());

		$response = $this->actingAs($this->userMayUpload1)->postJson('Sharing', [
			'user_ids' => [$this->userNoUpload->id],
			'group_ids' => [],
			'album_ids' => [$this->album1->id],
			'grants_edit' => true,
			'grants_move' => true,
			'grants_delete' => true,
			'grants_download' => true,
			'grants_full_photo_access' => true,
			'grants_upload' => true,
		]);
		$this->assertOk($response);
		self::assertEquals(3, AccessPermission::where(APC::BASE_ALBUM_ID, '=', $this->album1->id)->count());

		// Update sub album permission.
		$response = $this->actingAs($this->userMayUpload1)->putJson('Sharing', [
			'album_id' => $this->album1->id,
			'shall_override' => true,
		]);
		$this->assertNoContent($response);
		self::assertEquals(0,
			AccessPermission::query()
				->where(APC::BASE_ALBUM_ID, '=', $this->subAlbum1->id)
				->where(APC::USER_GROUP_ID, '=', $this->group2->id)
				->count());
		self::assertEquals(0,
			AccessPermission::query()
				->where(APC::BASE_ALBUM_ID, '=', $this->subAlbum1->id)
				->where(APC::USER_ID, '=', $this->userLocked->id)
				->count());
		self::assertEquals(1,
			AccessPermission::query()
				->where(APC::BASE_ALBUM_ID, '=', $this->subAlbum1->id)
				->where(APC::USER_ID, '=', $this->userMayUpload2->id)
				->count());
		self::assertEquals(1,
			AccessPermission::query()
				->where(APC::BASE_ALBUM_ID, '=', $this->subAlbum1->id)
				->where(APC::USER_ID, '=', $this->userNoUpload->id)
				->count());
	}
}