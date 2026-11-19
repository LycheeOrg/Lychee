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

namespace Tests\Feature_v3\Search;

use App\Actions\Search\SearchTokenParser;
use App\Actions\Search\StructOfArrays\QuerySearchAlbumRights;
use App\Actions\Search\StructOfArrays\QuerySearchAlbums;
use App\Http\Resources\V3\AlbumDataResource;
use App\Http\Resources\V3\AlbumRightsResource;
use App\Models\Album;
use Illuminate\Support\Facades\Auth;
use Spatie\LaravelData\Optional;
use Tests\Feature_v3\Base\BaseApiWithDataTest;

/**
 * Feature 069, I5 — the album tier: reuse of `BuildAlbumDataResource` against a
 * search-shaped query (FR-069-07, drift finding D3), heterogeneous-parent
 * rights (S-069-14) and the photo-only-token guard (S-069-20).
 */
class QuerySearchAlbumsTest extends BaseApiWithDataTest
{
	private function albums(string $raw, ?Album $origin = null): AlbumDataResource
	{
		return resolve(QuerySearchAlbums::class)->do(SearchTokenParser::parse($raw), $origin, Auth::user(), null);
	}

	private function rights(string $raw, ?Album $origin = null): AlbumRightsResource
	{
		return resolve(QuerySearchAlbumRights::class)->do(SearchTokenParser::parse($raw), $origin, Auth::user());
	}

	public function tearDown(): void
	{
		Auth::logout();
		parent::tearDown();
	}

	// ── FR-069-07 — the tier runs at all (drift finding D3) ─────────

	public function testAMatchingAlbumIsReturnedWithEveryParallelArrayAligned(): void
	{
		Auth::login($this->userMayUpload1);

		$result = $this->albums($this->album1->title);

		$index = array_search($this->album1->id, $result->ids, true);
		self::assertNotFalse($index);

		$count = count($result->ids);
		self::assertCount($count, $result->titles);
		self::assertCount($count, $result->descriptions);
		self::assertCount($count, $result->cover_ids);
		self::assertCount($count, $result->owner_ids);
		self::assertCount($count, $result->is_password_requireds);
		self::assertCount($count, $result->is_nsfws);
		self::assertCount($count, $result->num_photos);
		self::assertCount($count, $result->created_ats);

		self::assertSame($this->album1->title, $result->titles[$index]);
	}

	/**
	 * `BuildAlbumDataResource` selects `computed_access_permissions.password`,
	 * which `applyBrowsabilityFilter()` alone never joins (drift finding D3).
	 * This asserts the column actually resolves rather than blowing up.
	 */
	public function testPasswordDerivedFlagResolvesForEveryRow(): void
	{
		Auth::login($this->userMayUpload1);

		$result = $this->albums($this->album1->title);

		self::assertNotCount(0, $result->ids);
		foreach ($result->is_password_requireds as $flag) {
			self::assertIsBool($flag);
		}
	}

	public function testAnAlbumTheViewerCannotBrowseIsNotReturned(): void
	{
		Auth::login($this->userMayUpload1);

		// album3 belongs to userNoUpload and is not shared with anyone.
		$result = $this->albums($this->album3->title);

		self::assertNotContains($this->album3->id, $result->ids);
	}

	public function testOriginAlbumRestrictsAlbumMatchesToItsSubtree(): void
	{
		Auth::login($this->userMayUpload1);

		self::assertContains($this->subAlbum1->id, $this->albums($this->subAlbum1->title)->ids);
		self::assertNotContains($this->subAlbum1->id, $this->albums($this->subAlbum1->title, $this->album2)->ids);
	}

	// ── S-069-20 — photo-only tokens must not return every album ────

	public function testAPhotoOnlyTokenReturnsNoAlbumsRatherThanAllOfThem(): void
	{
		Auth::login($this->userMayUpload1);

		$result = $this->albums('rating:avg:>=3');

		self::assertCount(0, $result->ids);
	}

	// ── S-069-14 — heterogeneous-parent rights ──────────────────────

	public function testRightsOmitsOwnerIdAndForbidsChildMutation(): void
	{
		Auth::login($this->userMayUpload1);

		$rights = $this->rights($this->album1->title);

		self::assertInstanceOf(Optional::class, $rights->owner_id);
		self::assertFalse($rights->can_delete_children);
		self::assertFalse($rights->can_move_children);
	}

	public function testRightsArraysAreAlignedWithIds(): void
	{
		Auth::login($this->userMayUpload1);

		$rights = $this->rights($this->album1->title);

		self::assertNotCount(0, $rights->ids);
		self::assertCount(count($rights->ids), $rights->grants_edit);
		self::assertCount(count($rights->ids), $rights->grants_download);
	}

	public function testAdminReceivesAllGrantsWithoutOwnerId(): void
	{
		Auth::login($this->admin);

		$rights = $this->rights($this->album1->title);

		self::assertInstanceOf(Optional::class, $rights->owner_id);
		self::assertNotCount(0, $rights->ids);
		foreach ($rights->grants_edit as $granted) {
			self::assertTrue($granted);
		}
	}

	public function testRightsAndDataTiersCoverTheSameAlbumSet(): void
	{
		Auth::login($this->userMayUpload1);

		$data = $this->albums($this->album1->title);
		$rights = $this->rights($this->album1->title);

		sort($data->ids);
		$rights_ids = $rights->ids;
		sort($rights_ids);

		self::assertSame($data->ids, $rights_ids);
	}
}
