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

use App\Enum\SizeVariantType;
use App\Models\AccessPermission;
use App\Models\Album;
use App\Models\Configs;
use App\Models\Face;
use App\Models\Person;
use App\Models\Photo;
use App\Models\SizeVariant;
use App\Repositories\ConfigManager;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Tests\Feature_v3\Base\BaseApiWithDataTest;

/**
 * Feature 103: the frame flag and the header of `Album::head`, and the Open
 * Graph image of `/gallery/{album_id}`, are read without loading the album's
 * photos (FR-103-01, FR-103-02, NFR-103-02).
 */
class AlbumHeadPhotoCostTest extends BaseApiWithDataTest
{
	private const KEYS = [
		'mod_frame_enabled',
		'mod_frame_include_subalbums',
		'sm_card_album_source',
		'use_album_compact_header',
		'ai_vision_enabled',
		'ai_vision_face_enabled',
	];

	/** @var array<string,string> */
	private array $initial_values = [];

	private int $photos_retrieved = 0;

	public function setUp(): void
	{
		parent::setUp();
		foreach (self::KEYS as $key) {
			$this->initial_values[$key] = strval(Configs::query()->where('key', '=', $key)->value('value'));
		}
		Configs::set('mod_frame_enabled', '1');
		Configs::set('mod_frame_include_subalbums', '0');
		Configs::set('sm_card_album_source', 'header');
		Configs::set('use_album_compact_header', '0');

		Event::listen('eloquent.retrieved: ' . Photo::class, function (): void {
			$this->photos_retrieved++;
		});
	}

	public function tearDown(): void
	{
		foreach ($this->initial_values as $key => $value) {
			Configs::query()->where('key', '=', $key)->update(['value' => $value]);
		}
		resolve(ConfigManager::class)->invalidateCache();
		parent::tearDown();
	}

	private function albumWithPhotos(int $count): Album
	{
		$album = Album::factory()->as_root()->owned_by($this->userMayUpload1)->create();
		Photo::factory()->count($count)->owned_by($this->userMayUpload1)->in($album)->create();

		return $album;
	}

	private static function makePortrait(Photo $photo): void
	{
		DB::table('size_variants')->where('photo_id', '=', $photo->id)->update(['ratio' => 0.66]);
	}

	/**
	 * The header the resources return: the first variant above the original.
	 */
	private static function headerPathOf(Photo $photo): string
	{
		return SizeVariant::query()
			->where('photo_id', '=', $photo->id)
			->where('type', '=', SizeVariantType::MEDIUM2X)
			->value('short_path');
	}

	private function headUrl(string $album_id): ?string
	{
		$response = $this->getJsonWithData('Album::head', ['album_id' => $album_id]);
		$this->assertOk($response);

		return $response->json('resource.preFormattedData.url');
	}

	private function assertHeaderIs(Photo $photo, ?string $url): void
	{
		self::assertNotNull($url);
		self::assertStringContainsString(self::headerPathOf($photo), $url);
	}

	/**
	 * @return array{queries:int,max_bindings:int,photos:int}
	 */
	private function measure(callable $request): array
	{
		$this->photos_retrieved = 0;
		DB::flushQueryLog();
		DB::enableQueryLog();
		$request();
		$log = DB::getQueryLog();
		DB::disableQueryLog();

		return [
			'queries' => count($log),
			'max_bindings' => max(array_map(fn (array $q) => count($q['bindings']), $log)),
			'photos' => $this->photos_retrieved,
		];
	}

	// ── Header (S-103-01 … S-103-04) ────────────────────────────────

	public function testHeaderIsOneOfTheAlbumPhotos(): void
	{
		$url = $this->actingAs($this->userMayUpload1)->headUrl($this->album1->id);

		self::assertNotNull($url);
		self::assertTrue(
			str_contains($url, self::headerPathOf($this->photo1)) || str_contains($url, self::headerPathOf($this->photo1b)),
			'The header is not a photo of the album.'
		);
	}

	public function testHeaderIsPickedAmongPhotosVisibleToTheGuest(): void
	{
		$album = Album::factory()->as_root()->owned_by($this->userMayUpload1)->create();
		AccessPermission::factory()->public()->visible()->for_album($album)->create();
		$visible = Photo::factory()->owned_by($this->userMayUpload1)->in($album)->create();
		self::makePortrait($visible);
		Photo::factory()->owned_by($this->userMayUpload1)->in($album)->create(['is_validated' => false]);

		$this->assertHeaderIs($visible, $this->headUrl($album->id));
	}

	public function testHeaderFallsBackToAPhotoWithoutLandscapeVariant(): void
	{
		$album = Album::factory()->as_root()->owned_by($this->userMayUpload1)->create();
		$photo = Photo::factory()->owned_by($this->userMayUpload1)->in($album)->create();
		self::makePortrait($photo);

		$this->assertHeaderIs($photo, $this->actingAs($this->userMayUpload1)->headUrl($album->id));
	}

	public function testHeaderIdWins(): void
	{
		DB::table('albums')->where('id', '=', $this->album1->id)->update(['header_id' => $this->photo1b->id]);

		$this->assertHeaderIs($this->photo1b, $this->actingAs($this->userMayUpload1)->headUrl($this->album1->id));
	}

	// ── Empty album and frame flag (S-103-05, S-103-06) ─────────────

	public function testEmptyAlbumHasNoHeaderAndNoFrame(): void
	{
		$album = Album::factory()->as_root()->owned_by($this->userMayUpload1)->create();

		$response = $this->actingAs($this->userMayUpload1)->getJsonWithData('Album::head', ['album_id' => $album->id]);
		$this->assertOk($response);
		self::assertNull($response->json('resource.preFormattedData.url'));
		self::assertFalse($response->json('config.is_mod_frame_enabled'));
	}

	public function testAlbumWithOnlyHiddenPhotosHasNoHeaderAndNoFrameForTheGuest(): void
	{
		$album = Album::factory()->as_root()->owned_by($this->userMayUpload1)->create();
		AccessPermission::factory()->public()->visible()->for_album($album)->create();
		Photo::factory()->owned_by($this->userMayUpload1)->in($album)->create(['is_validated' => false]);

		$response = $this->getJsonWithData('Album::head', ['album_id' => $album->id]);
		$this->assertOk($response);
		self::assertNull($response->json('resource.preFormattedData.url'));
		self::assertFalse($response->json('config.is_mod_frame_enabled'));
	}

	public function testAlbumWithPhotosHasFrame(): void
	{
		$response = $this->actingAs($this->userMayUpload1)->getJsonWithData('Album::head', ['album_id' => $this->album1->id]);
		$this->assertOk($response);
		self::assertTrue($response->json('config.is_mod_frame_enabled'));
	}

	// ── Tag, person and smart albums (S-103-07, S-103-08) ───────────

	public function testTagAlbumHeaderIsATaggedPhoto(): void
	{
		$this->assertHeaderIs($this->photo1, $this->actingAs($this->userMayUpload1)->headUrl($this->tagAlbum1->id));
	}

	public function testPersonAlbumHeaderIsAPhotoOfThePerson(): void
	{
		Configs::set('ai_vision_enabled', '1');
		Configs::set('ai_vision_face_enabled', '1');
		$person = Person::factory()->create(['name' => 'Alice', 'is_searchable' => true]);
		Face::factory()->for_photo($this->photo1b)->for_person($person)->create();

		$response = $this->actingAs($this->userMayUpload1)->postJson('PersonAlbum', [
			'title' => 'person_album',
			'persons' => [$person->id],
			'is_and' => false,
		]);
		$this->assertOk($response);

		$this->assertHeaderIs($this->photo1b, $this->headUrl($response->getOriginalContent()));
	}

	public function testSmartAlbumHeaderIsFromItsPhotos(): void
	{
		$this->assertHeaderIs($this->photoUnsorted, $this->actingAs($this->userMayUpload1)->headUrl('unsorted'));
	}

	// ── Open Graph image (S-103-09) ─────────────────────────────────

	public function testGalleryPageOpenGraphImageIsTheHeader(): void
	{
		$album = Album::factory()->as_root()->owned_by($this->userMayUpload1)->create();
		$photo = Photo::factory()->owned_by($this->userMayUpload1)->in($album)->create();

		$response = $this->actingAs($this->userMayUpload1)->get('/gallery/' . $album->id);
		$this->assertOk($response);
		$response->assertSee('<meta property="og:image" content="', false);
		$response->assertSee(self::headerPathOf($photo), false);
	}

	// ── Cost does not grow with the photo count (S-103-10) ──────────

	public function testHeadCostDoesNotGrowWithPhotoCount(): void
	{
		$small = $this->albumWithPhotos(4);
		$large = $this->albumWithPhotos(24);
		$this->actingAs($this->userMayUpload1)->headUrl($small->id);

		$cost_small = $this->measure(fn () => $this->headUrl($small->id));
		$cost_large = $this->measure(fn () => $this->headUrl($large->id));

		self::assertSame($cost_small, $cost_large);
		self::assertLessThan(24, $cost_large['max_bindings']);
	}

	public function testGalleryPageCostDoesNotGrowWithPhotoCount(): void
	{
		$small = $this->albumWithPhotos(4);
		$large = $this->albumWithPhotos(24);
		$this->actingAs($this->userMayUpload1)->get('/gallery/' . $small->id);

		$cost_small = $this->measure(fn () => $this->assertOk($this->get('/gallery/' . $small->id)));
		$cost_large = $this->measure(fn () => $this->assertOk($this->get('/gallery/' . $large->id)));

		self::assertSame($cost_small, $cost_large);
		self::assertLessThan(24, $cost_large['max_bindings']);
	}
}
