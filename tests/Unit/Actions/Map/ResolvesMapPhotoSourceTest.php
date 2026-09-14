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

namespace Tests\Unit\Actions\Map;

use App\Actions\Album\PositionData as AlbumPositionData;
use App\Actions\Albums\PositionData as RootPositionData;
use App\Actions\Map\ResolvesMapPhotoSource;
use App\Models\Configs;
use App\Models\Photo;
use App\Repositories\ConfigManager;
use Tests\Feature_v3\Base\BaseApiWithDataTest;

/**
 * T-067-04: asserts {@see ResolvesMapPhotoSource}'s root/album query
 * resolution returns the exact same candidate photo ids as today's
 * {@see RootPositionData}/{@see AlbumPositionData} (FR-067-03, FR-067-04,
 * S-067-02).
 */
class ResolvesMapPhotoSourceTest extends BaseApiWithDataTest
{
	public function setUp(): void
	{
		parent::setUp();
		// Actions/resources built directly (bypassing the HTTP kernel's
		// ResolveConfigs middleware) still need the `configs` request
		// attribute populated - mirrors MetaTest's own setup for the same
		// reason.
		request()->attributes->set('configs', app(ConfigManager::class));
	}

	/**
	 * @param iterable<int,Photo> $photos
	 *
	 * @return string[]
	 */
	private function sortedIds(iterable $photos): array
	{
		$ids = [];
		foreach ($photos as $photo) {
			$ids[] = $photo->id;
		}
		sort($ids);

		return $ids;
	}

	public function testRootQueryMatchesPositionDataForAuthenticatedUser(): void
	{
		$this->actingAs($this->userMayUpload1);
		$probe = new MapPhotoSourceProbe();

		$resolved_ids = $this->sortedIds($probe->rootQuery($this->userMayUpload1)->get());
		$legacy_ids = $this->sortedIds(app(RootPositionData::class)->do()->photos);

		self::assertSame($legacy_ids, $resolved_ids);
		self::assertNotEmpty($resolved_ids, 'fixture must contain at least one geotagged photo for this comparison to be meaningful');
	}

	public function testRootQueryMatchesPositionDataForGuest(): void
	{
		$probe = new MapPhotoSourceProbe();

		$resolved_ids = $this->sortedIds($probe->rootQuery(null)->get());
		$legacy_ids = $this->sortedIds(app(RootPositionData::class)->do()->photos);

		self::assertSame($legacy_ids, $resolved_ids);
	}

	public function testRootQueryHonoursHideNsfwInMapConfig(): void
	{
		Configs::set('hide_nsfw_in_map', '1');
		$this->actingAs($this->userMayUpload1);
		$probe = new MapPhotoSourceProbe();

		$resolved_ids = $this->sortedIds($probe->rootQuery($this->userMayUpload1)->get());
		$legacy_ids = $this->sortedIds(app(RootPositionData::class)->do()->photos);

		self::assertSame($legacy_ids, $resolved_ids);
	}

	public function testAlbumQueryWithoutSubAlbumsMatchesPositionData(): void
	{
		$this->actingAs($this->userMayUpload1);
		$probe = new MapPhotoSourceProbe();

		$resolved_ids = $this->sortedIds($probe->albumQuery($this->album1, false)->get());
		$legacy_ids = $this->sortedIds(app(AlbumPositionData::class)->get($this->album1, false)->photos);

		self::assertSame($legacy_ids, $resolved_ids);
		// album1 has photo1/photo1b directly, subPhoto1 only in subAlbum1 - must not leak in.
		self::assertNotContains($this->subPhoto1->id, $resolved_ids);
	}

	public function testAlbumQueryWithSubAlbumsMatchesPositionData(): void
	{
		$this->actingAs($this->userMayUpload1);
		$probe = new MapPhotoSourceProbe();

		$resolved_ids = $this->sortedIds($probe->albumQuery($this->album1, true)->get());
		$legacy_ids = $this->sortedIds(app(AlbumPositionData::class)->get($this->album1, true)->photos);

		self::assertSame($legacy_ids, $resolved_ids);
		self::assertContains($this->subPhoto1->id, $resolved_ids);
	}
}
