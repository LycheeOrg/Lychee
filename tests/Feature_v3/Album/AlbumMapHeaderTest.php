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

use App\Contracts\Models\AbstractAlbum;
use App\Http\Controllers\Gallery\AlbumController;
use App\Http\Resources\Traits\HasHeaderUrl;
use App\Models\Album;
use App\Models\Configs;
use App\Models\Photo;
use App\Models\SizeVariant;
use App\Repositories\ConfigManager;
use Illuminate\Support\Facades\DB;
use Tests\Feature_v3\Base\BaseApiWithDataTest;

/**
 * Feature 086: the `map` header style — `PATCH /Album` flag (FR-086-01,
 * FR-086-02), set-as-header (FR-086-11), the album head map-or-image
 * decision (FR-086-03, FR-086-04) and the social card image (FR-086-12).
 */
class AlbumMapHeaderTest extends BaseApiWithDataTest
{
	private const KEYS = [
		'map_display',
		'map_display_public',
		'map_include_subalbums',
		'use_album_compact_header',
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
		Configs::set('use_album_compact_header', '0');
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
	 * @return array<string,mixed>
	 */
	private function albumPayload(array $header): array
	{
		return array_merge([
			'album_id' => $this->album1->id,
			'title' => 'title',
			'license' => 'none',
			'description' => '',
			'photo_sorting_column' => 'title',
			'photo_sorting_order' => 'ASC',
			'album_sorting_column' => 'title',
			'album_sorting_order' => 'DESC',
			'album_aspect_ratio' => '1/1',
			'photo_layout' => null,
			'copyright' => '',
			'is_compact' => false,
			'is_map_header' => false,
			'is_pinned' => false,
			'header_id' => null,
			'cover_id' => null,
			'album_timeline' => null,
			'photo_timeline' => null,
		], $header);
	}

	private function setMapHeader(Album $album): void
	{
		DB::table('albums')->where('id', '=', $album->id)->update(['header_id' => AlbumController::MAP_HEADER]);
	}

	private static function headerIdOf(Album $album): ?string
	{
		$header_id = DB::table('albums')->where('id', '=', $album->id)->value('header_id');

		return $header_id === null ? null : trim($header_id);
	}

	// ── PATCH /Album (S-086-01 … S-086-03, S-086-17) ────────────────

	public function testPatchStoresMapHeaderAndClearsFocus(): void
	{
		DB::table('albums')->where('id', '=', $this->album1->id)->update([
			'header_id' => $this->photo1->id,
			'header_photo_focus' => json_encode(['x' => 0.5, 'y' => 0.5]),
		]);

		$response = $this->actingAs($this->userMayUpload1)->patchJson('Album', $this->albumPayload(['is_map_header' => true]));
		$this->assertOk($response);

		self::assertSame(AlbumController::MAP_HEADER, self::headerIdOf($this->album1));
		self::assertNull(DB::table('albums')->where('id', '=', $this->album1->id)->value('header_photo_focus'));
	}

	public function testPatchWithCompactAndMapIsUnprocessable(): void
	{
		$response = $this->actingAs($this->userMayUpload1)->patchJson('Album', $this->albumPayload(['is_compact' => true, 'is_map_header' => true]));
		$this->assertUnprocessable($response);
	}

	public function testPatchWithoutMapFlagIsUnprocessable(): void
	{
		$payload = $this->albumPayload([]);
		unset($payload['is_map_header']);

		$response = $this->actingAs($this->userMayUpload1)->patchJson('Album', $payload);
		$this->assertUnprocessable($response);
	}

	public function testPatchWithMapFlagOffFollowsCompactAndHeaderId(): void
	{
		$this->setMapHeader($this->album1);

		$response = $this->actingAs($this->userMayUpload1)->patchJson('Album', $this->albumPayload(['is_compact' => true]));
		$this->assertOk($response);
		self::assertSame(AlbumController::COMPACT_HEADER, self::headerIdOf($this->album1));

		$this->setMapHeader($this->album1);

		$response = $this->actingAs($this->userMayUpload1)->patchJson('Album', $this->albumPayload([]));
		$this->assertOk($response);
		self::assertNull(self::headerIdOf($this->album1));
	}

	public function testEditableResourceReturnsMapHeaderId(): void
	{
		$response = $this->actingAs($this->userMayUpload1)->patchJson('Album', $this->albumPayload(['is_map_header' => true]));
		$this->assertOk($response);
		$response->assertJson(['header_id' => AlbumController::MAP_HEADER]);
	}

	// ── Set as header (S-086-14) ────────────────────────────────────

	public function testSetPhotoAsHeaderReplacesMapHeader(): void
	{
		$this->setMapHeader($this->album1);

		$response = $this->actingAs($this->userMayUpload1)->postJson('Album::header', [
			'album_id' => $this->album1->id,
			'header_id' => $this->photo1->id,
			'is_compact' => false,
		]);
		$this->assertNoContent($response);

		self::assertSame($this->photo1->id, self::headerIdOf($this->album1));
	}

	// ── Album head decision (S-086-04 … S-086-09) ───────────────────

	public function testHeadShowsMapForGeotaggedAlbum(): void
	{
		$this->setMapHeader($this->album1);

		$response = $this->actingAs($this->userMayUpload1)->getJsonWithData('Album::head', ['album_id' => $this->album1->id]);
		$this->assertOk($response);
		$response->assertJson(['resource' => ['header_id' => AlbumController::MAP_HEADER, 'preFormattedData' => ['is_map_header' => true, 'url' => null]]]);
	}

	public function testHeadIsNotMapForOtherHeaderStyles(): void
	{
		$response = $this->actingAs($this->userMayUpload1)->getJsonWithData('Album::head', ['album_id' => $this->album1->id]);
		$this->assertOk($response);
		$response->assertJson(['resource' => ['preFormattedData' => ['is_map_header' => false]]]);
		self::assertNotNull($response->json('resource.preFormattedData.url'));
	}

	public function testHeadFallsBackWithoutGeotaggedPhoto(): void
	{
		$album = Album::factory()->as_root()->owned_by($this->userMayUpload1)->create();
		Photo::factory()->owned_by($this->userMayUpload1)->in($album)->create();
		$this->setMapHeader($album);

		$response = $this->actingAs($this->userMayUpload1)->getJsonWithData('Album::head', ['album_id' => $album->id]);
		$this->assertOk($response);
		$response->assertJson(['resource' => ['preFormattedData' => ['is_map_header' => false]]]);
		self::assertNotNull($response->json('resource.preFormattedData.url'));
	}

	public function testHeadFallsBackWhenMapDisabled(): void
	{
		Configs::set('map_display', '0');
		$this->setMapHeader($this->album1);

		$response = $this->actingAs($this->userMayUpload1)->getJsonWithData('Album::head', ['album_id' => $this->album1->id]);
		$this->assertOk($response);
		$response->assertJson(['resource' => ['preFormattedData' => ['is_map_header' => false]]]);
		self::assertNotNull($response->json('resource.preFormattedData.url'));
	}

	public function testHeadForGuestFollowsPublicMapSetting(): void
	{
		$this->setMapHeader($this->album4);

		$response = $this->getJsonWithData('Album::head', ['album_id' => $this->album4->id]);
		$this->assertOk($response);
		$response->assertJson(['resource' => ['preFormattedData' => ['is_map_header' => true, 'url' => null]]]);

		Configs::set('map_display_public', '0');

		$response = $this->getJsonWithData('Album::head', ['album_id' => $this->album4->id]);
		$this->assertOk($response);
		$response->assertJson(['resource' => ['preFormattedData' => ['is_map_header' => false]]]);
		self::assertNotNull($response->json('resource.preFormattedData.url'));
	}

	public function testHeadIsCompactWhenCompactHeadersAreForced(): void
	{
		Configs::set('use_album_compact_header', '1');
		$this->setMapHeader($this->album1);

		$response = $this->actingAs($this->userMayUpload1)->getJsonWithData('Album::head', ['album_id' => $this->album1->id]);
		$this->assertOk($response);
		$response->assertJson(['resource' => ['preFormattedData' => ['is_map_header' => false, 'url' => null]]]);
	}

	public function testHeadCountsSubAlbumPhotosOnlyWhenIncluded(): void
	{
		$album = Album::factory()->as_root()->owned_by($this->userMayUpload1)->create();
		Photo::factory()->owned_by($this->userMayUpload1)->in($album)->create();
		$sub_album = Album::factory()->children_of($album)->owned_by($this->userMayUpload1)->create();
		Photo::factory()->owned_by($this->userMayUpload1)->with_GPS_coordinates()->in($sub_album)->create();
		$this->setMapHeader($album);

		$response = $this->actingAs($this->userMayUpload1)->getJsonWithData('Album::head', ['album_id' => $album->id]);
		$this->assertOk($response);
		$response->assertJson(['resource' => ['preFormattedData' => ['is_map_header' => false]]]);

		Configs::set('map_include_subalbums', '1');

		$response = $this->actingAs($this->userMayUpload1)->getJsonWithData('Album::head', ['album_id' => $album->id]);
		$this->assertOk($response);
		$response->assertJson(['resource' => ['preFormattedData' => ['is_map_header' => true, 'url' => null]]]);
	}

	// ── Social card (S-086-15) ──────────────────────────────────────

	public function testHeaderImageOfMapAlbumIsARandomPhoto(): void
	{
		$this->setMapHeader($this->album1);
		$this->actingAs($this->userMayUpload1);
		$album = Album::query()->findOrFail($this->album1->id);
		// Called outside an HTTP request, as the Meta component is.
		request()->attributes->set('configs', resolve(ConfigManager::class));

		$resolver = new class() {
			use HasHeaderUrl;

			public function resolve(AbstractAlbum $album): ?SizeVariant
			{
				return $this->getHeaderUrl($album);
			}
		};

		$size_variant = $resolver->resolve($album);
		self::assertNotNull($size_variant);
		self::assertContains($size_variant->photo_id, [$this->photo1->id, $this->photo1b->id]);
	}
}
