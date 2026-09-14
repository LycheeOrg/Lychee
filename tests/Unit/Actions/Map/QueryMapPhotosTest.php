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

use App\Actions\Map\QueryMapPhotos;
use App\DTO\MapViewport;
use App\Models\Album;
use App\Models\Photo;
use App\Repositories\ConfigManager;
use Illuminate\Support\Carbon;
use Tests\Feature_v3\Base\BaseApiWithDataTest;

/**
 * T-067-10: covers {@see QueryMapPhotos} — the dense-cell exclusion
 * (NFR-067-05), sparse-cell field accuracy, and both scopes' `album_ids[]`
 * tie-break rules (Q-067-15, S-067-06, S-067-07, S-067-09).
 */
class QueryMapPhotosTest extends BaseApiWithDataTest
{
	public function setUp(): void
	{
		parent::setUp();
		request()->attributes->set('configs', app(ConfigManager::class));
	}

	public function testDenseCellIsEntirelyAbsentFromResponse(): void
	{
		$album = Album::factory()->as_root()->owned_by($this->userMayUpload1)->create();
		for ($i = 0; $i < QueryMapPhotos::LEAF_THRESHOLD + 1; $i++) {
			Photo::factory()->owned_by($this->userMayUpload1)->in($album)->create([
				'latitude' => '10.000' . str_pad((string) $i, 2, '0', STR_PAD_LEFT),
				'longitude' => '10.000' . str_pad((string) $i, 2, '0', STR_PAD_LEFT),
			]);
		}

		$this->actingAs($this->userMayUpload1);
		$viewport = new MapViewport(north: 45.0, south: 0.0, east: 45.0, west: 0.0, zoom: 4);
		$resource = app(QueryMapPhotos::class)->do($album, $this->userMayUpload1, $viewport, false);

		self::assertSame([], $resource->ids, 'a cell with count > LEAF_THRESHOLD must contribute zero entries');
	}

	public function testSparseCellIsFullyPresentWithAccurateFields(): void
	{
		$album = Album::factory()->as_root()->owned_by($this->userMayUpload1)->create();
		$photo = Photo::factory()->owned_by($this->userMayUpload1)->in($album)->with_title('Eiffel Tower')->create([
			'latitude' => '48.8584',
			'longitude' => '2.2945',
			'taken_at' => new Carbon('2023-06-15 10:30:00'),
		]);

		$this->actingAs($this->userMayUpload1);
		$viewport = new MapViewport(north: 50.0, south: 45.0, east: 5.0, west: 0.0, zoom: 4);
		$resource = app(QueryMapPhotos::class)->do($album, $this->userMayUpload1, $viewport, false);

		self::assertSame([$photo->id], $resource->ids);
		self::assertSame([$album->id], $resource->album_ids);
		self::assertSame(['Eiffel Tower'], $resource->titles);
		self::assertEqualsWithDelta(48.8584, $resource->latitudes[0], 1e-4);
		self::assertEqualsWithDelta(2.2945, $resource->longitudes[0], 1e-4);
		self::assertNotNull($resource->taken_ats[0]);
	}

	/**
	 * Album scope, `include_sub_albums=true`: a photo living deeper in the
	 * subtree resolves to that in-scope sub-album, not the top requested
	 * album (Q-067-15, lowest `_lft` tie-break).
	 */
	public function testAlbumScopeWithSubAlbumsResolvesToInScopeSubAlbum(): void
	{
		$root = Album::factory()->as_root()->owned_by($this->userMayUpload1)->create();
		$sub = Album::factory()->children_of($root)->owned_by($this->userMayUpload1)->create();
		$photo = Photo::factory()->owned_by($this->userMayUpload1)->in($sub)->create(['latitude' => '10.0', 'longitude' => '10.0']);

		$this->actingAs($this->userMayUpload1);
		$viewport = new MapViewport(north: 45.0, south: 0.0, east: 45.0, west: 0.0, zoom: 4);
		$resource = app(QueryMapPhotos::class)->do($root, $this->userMayUpload1, $viewport, true);

		self::assertSame([$photo->id], $resource->ids);
		self::assertSame([$sub->id], $resource->album_ids);
	}

	/**
	 * Root scope: a photo belonging to several albums, one of which the
	 * viewer cannot access, resolves only to an album the viewer can
	 * actually access (S-067-09).
	 */
	public function testRootScopeResolvesOnlyToViewerAccessibleAlbum(): void
	{
		// photoUnsorted (base fixture) belongs to no album at all - album_ids[i] must be null.
		$this->photoUnsorted->latitude = 20.0;
		$this->photoUnsorted->longitude = 20.0;
		$this->photoUnsorted->save();

		// photo3 (owned by userNoUpload, inside album3 which userMayUpload1 cannot access)
		// gets an additional membership in album1 (owned by + accessible to userMayUpload1).
		$this->photo3->albums()->attach($this->album1->id);
		$this->photo3->latitude = 10.0;
		$this->photo3->longitude = 10.0;
		$this->photo3->save();

		$this->actingAs($this->userMayUpload1);
		$viewport = new MapViewport(north: 45.0, south: 0.0, east: 45.0, west: 0.0, zoom: 4);
		$resource = app(QueryMapPhotos::class)->do(null, $this->userMayUpload1, $viewport, false);

		$by_id = array_combine($resource->ids, $resource->album_ids);
		self::assertArrayHasKey($this->photo3->id, $by_id);
		// Only album1 (accessible) may be resolved - never album3 (inaccessible to this viewer).
		self::assertSame($this->album1->id, $by_id[$this->photo3->id]);
	}
}
