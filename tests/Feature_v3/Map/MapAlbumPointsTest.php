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

namespace Tests\Feature_v3\Map;

use App\Models\Album;
use App\Models\Configs;
use App\Models\Photo;
use App\Repositories\ConfigManager;
use Illuminate\Support\Facades\DB;
use Tests\Feature_v3\Base\BaseApiWithDataTest;

/**
 * Feature 086: `GET /api/v3/Map/album` — every geotagged photo point of an
 * album for the map header (FR-086-05, FR-086-06, NFR-086-01).
 */
class MapAlbumPointsTest extends BaseApiWithDataTest
{
	private const KEYS = [
		'map_display',
		'map_display_public',
		'map_include_subalbums',
	];

	/** @var array<string,string> */
	private array $initial_values = [];

	public function setUp(): void
	{
		parent::setUp();
		foreach (self::KEYS as $key) {
			$this->initial_values[$key] = strval(Configs::query()->where('key', '=', $key)->value('value'));
		}
		Configs::set('map_display', '1');
		Configs::set('map_display_public', '1');
		Configs::set('map_include_subalbums', '0');
	}

	public function tearDown(): void
	{
		foreach ($this->initial_values as $key => $value) {
			Configs::query()->where('key', '=', $key)->update(['value' => $value]);
		}
		resolve(ConfigManager::class)->invalidateCache();
		parent::tearDown();
	}

	/**
	 * @return array<string,string|null>
	 */
	private static function albumIdByPhotoId(array $json): array
	{
		return array_combine($json['ids'], $json['album_ids']);
	}

	// ── Validation (S-086-12) ───────────────────────────────────────

	public function testMissingAlbumIdIsUnprocessable(): void
	{
		$response = $this->actingAs($this->userMayUpload1)->getJsonV3('Map/album');
		$this->assertUnprocessable($response);
	}

	// ── Success (S-086-09, S-086-10, S-086-13) ──────────────────────

	public function testReturnsOwnPhotosOnlyWhenSubAlbumsAreExcluded(): void
	{
		$response = $this->actingAs($this->userMayUpload1)->getJsonV3('Map/album', ['album_id' => $this->album1->id]);
		$this->assertOk($response);

		self::assertEqualsCanonicalizing(
			[$this->photo1->id => $this->album1->id, $this->photo1b->id => $this->album1->id],
			self::albumIdByPhotoId($response->json()),
		);
		self::assertCount(2, $response->json('latitudes'));
		self::assertCount(2, $response->json('longitudes'));
	}

	public function testReturnsEachSubAlbumPhotoOnceWithItsAlbum(): void
	{
		Configs::set('map_include_subalbums', '1');
		// photo1 is also linked into subAlbum1: listed once, under album1 (lowest _lft).
		DB::table('photo_album')->insert(['photo_id' => $this->photo1->id, 'album_id' => $this->subAlbum1->id]);

		$response = $this->actingAs($this->userMayUpload1)->getJsonV3('Map/album', ['album_id' => $this->album1->id]);
		$this->assertOk($response);

		$json = $response->json();
		self::assertCount(3, $json['ids']);
		self::assertEqualsCanonicalizing(
			[
				$this->photo1->id => $this->album1->id,
				$this->photo1b->id => $this->album1->id,
				$this->subPhoto1->id => $this->subAlbum1->id,
			],
			self::albumIdByPhotoId($json),
		);
	}

	public function testCoordinatesMatchThePhoto(): void
	{
		$album = Album::factory()->as_root()->owned_by($this->userMayUpload1)->create();
		$photo = Photo::factory()->owned_by($this->userMayUpload1)->in($album)->create(['latitude' => '12.5', 'longitude' => '-3.25']);

		$response = $this->actingAs($this->userMayUpload1)->getJsonV3('Map/album', ['album_id' => $album->id]);
		$this->assertOk($response);
		$response->assertExactJson(['ids' => [$photo->id], 'album_ids' => [$album->id], 'latitudes' => [12.5], 'longitudes' => [-3.25]]);
	}

	public function testAlbumWithoutGeotaggedPhotoReturnsEmptyArrays(): void
	{
		$album = Album::factory()->as_root()->owned_by($this->userMayUpload1)->create();
		Photo::factory()->owned_by($this->userMayUpload1)->in($album)->create();

		$response = $this->actingAs($this->userMayUpload1)->getJsonV3('Map/album', ['album_id' => $album->id]);
		$this->assertOk($response);
		$response->assertExactJson(['ids' => [], 'album_ids' => [], 'latitudes' => [], 'longitudes' => []]);
	}

	public function testWorksWithStructOfArrayFlagOff(): void
	{
		config(['features.struct-of-array' => false]);

		$response = $this->actingAs($this->userMayUpload1)->getJsonV3('Map/album', ['album_id' => $this->album1->id]);
		$this->assertOk($response);
		self::assertCount(2, $response->json('ids'));
	}

	public function testGuestGetsPublicAlbumPoints(): void
	{
		$response = $this->getJsonV3('Map/album', ['album_id' => $this->album4->id]);
		$this->assertOk($response);
		$response->assertJson(['ids' => [$this->photo4->id], 'album_ids' => [$this->album4->id]]);
	}

	// ── Gating (S-086-11) ───────────────────────────────────────────

	public function testGuestIsRefusedWithoutPublicMap(): void
	{
		Configs::set('map_display_public', '0');

		$response = $this->getJsonV3('Map/album', ['album_id' => $this->album4->id]);
		$this->assertUnauthorized($response);
	}

	public function testMapDisplayDisabledIsForbidden(): void
	{
		Configs::set('map_display', '0');

		$response = $this->actingAs($this->userMayUpload1)->getJsonV3('Map/album', ['album_id' => $this->album1->id]);
		$this->assertForbidden($response);
	}

	public function testInaccessibleAlbumIsForbidden(): void
	{
		$response = $this->actingAs($this->userNoUpload)->getJsonV3('Map/album', ['album_id' => $this->album1->id]);
		$this->assertForbidden($response);
	}
}
