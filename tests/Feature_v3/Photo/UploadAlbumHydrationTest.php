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

namespace Tests\Feature_v3\Photo;

use App\Constants\PhotoAlbum as PA;
use App\DTO\ImportMode;
use App\Enum\SmartAlbumType;
use App\Image\Files\NativeLocalFile;
use App\Image\Files\ProcessableJobFile;
use App\Jobs\ImportImageJob;
use App\Jobs\ProcessImageJob;
use App\Models\Album;
use App\Models\Photo;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Tests\Constants\TestConstants;
use Tests\Feature_v3\Base\BaseApiWithDataTest;

/**
 * Feature 090 (FR-090-01 … FR-090-03): adding a photo to an album never
 * hydrates the photos or the child albums already in that album.
 *
 * Album models eager-load their cover photos ({@see Album::$with}), a fixed
 * number per album, so those are left out of the photos checked.
 */
class UploadAlbumHydrationTest extends BaseApiWithDataTest
{
	/** @var string[] */
	private array $album1_photo_ids = [];

	/** @var array<string,true> */
	private array $retrieved_photo_ids = [];

	/** @var array<string,true> */
	private array $retrieved_album_ids = [];

	public function setUp(): void
	{
		parent::setUp();

		// More photos than cover slots, so some photos of album1 are never a cover.
		$extra_photos = Photo::factory()->count(3)->owned_by($this->userMayUpload1)->in($this->album1)->create();
		$this->album1_photo_ids = array_merge([$this->photo1->id, $this->photo1b->id], $extra_photos->pluck('id')->all());

		Event::listen('eloquent.retrieved: ' . Photo::class, function (Photo $photo): void {
			$this->retrieved_photo_ids[$photo->id] = true;
		});
		Event::listen('eloquent.retrieved: ' . Album::class, function (Album $album): void {
			$this->retrieved_album_ids[$album->id] = true;
		});
	}

	/**
	 * S-090-01: chunk request and ProcessImageJob (sync queue) on a regular album.
	 */
	public function testUploadIntoAlbum(): void
	{
		$covers_before = $this->album1CoverIds();

		$response = $this->actingAs($this->userMayUpload1)->upload('Photo', filename: TestConstants::SAMPLE_FILE_NIGHT_IMAGE, album_id: $this->album1->id);
		$this->assertCreated($response);

		$this->assertAlbum1NotHydrated($covers_before);
	}

	/**
	 * S-090-02: upload into the Highlighted smart album.
	 */
	public function testUploadIntoHighlightedSmartAlbum(): void
	{
		$this->photo1->is_highlighted = true;
		$this->photo1->save();

		$response = $this->actingAs($this->userMayUpload1)->upload('Photo', filename: TestConstants::SAMPLE_FILE_NIGHT_IMAGE, album_id: SmartAlbumType::HIGHLIGHTED->value);
		$this->assertCreated($response);

		$this->assertNotHydrated([$this->photo1->id], []);
	}

	/**
	 * S-090-03: ProcessImageJob built from an album ID.
	 */
	public function testProcessImageJobFromAlbumId(): void
	{
		$covers_before = $this->album1CoverIds();
		$this->actingAs($this->userMayUpload1);
		$file = new ProcessableJobFile('.jpg', 'night.jpg');
		$file->write(fopen(base_path(TestConstants::SAMPLE_FILE_NIGHT_IMAGE), 'r'));
		$file->close();

		dispatch_sync(new ProcessImageJob($file, $this->album1->id, null));

		$this->assertAlbum1NotHydrated($covers_before);
	}

	/**
	 * S-090-04: ImportImageJob (server import, zip extraction).
	 */
	public function testImportImageJob(): void
	{
		$covers_before = $this->album1CoverIds();
		$this->actingAs($this->userMayUpload1);
		$file = new NativeLocalFile(base_path(TestConstants::SAMPLE_FILE_NIGHT_IMAGE));

		dispatch_sync(new ImportImageJob($file, $this->userMayUpload1->id, new ImportMode(), $this->album1));

		$this->assertAlbum1NotHydrated($covers_before);
	}

	/**
	 * The new photo is in album1, and neither the photos already in album1
	 * (apart from its covers before and after the add) nor its child album
	 * were hydrated.
	 *
	 * @param string[] $covers_before
	 */
	private function assertAlbum1NotHydrated(array $covers_before): void
	{
		self::assertSame(count($this->album1_photo_ids) + 1, DB::table(PA::PHOTO_ALBUM)->where(PA::ALBUM_ID, '=', $this->album1->id)->count());

		$photo_ids = array_values(array_diff($this->album1_photo_ids, $covers_before, $this->album1CoverIds()));
		self::assertNotEmpty($photo_ids);

		$this->assertNotHydrated($photo_ids, [$this->subAlbum1->id]);
	}

	/**
	 * @return string[]
	 */
	private function album1CoverIds(): array
	{
		$album = DB::table('albums')->where('id', '=', $this->album1->id)->first(['cover_id', 'auto_cover_id_max_privilege', 'auto_cover_id_least_privilege']);

		return array_values(array_filter(
			[$album->cover_id, $album->auto_cover_id_max_privilege, $album->auto_cover_id_least_privilege],
			fn (?string $id) => $id !== null
		));
	}

	/**
	 * @param string[] $photo_ids
	 * @param string[] $album_ids
	 */
	private function assertNotHydrated(array $photo_ids, array $album_ids): void
	{
		self::assertSame([], array_values(array_intersect($photo_ids, array_keys($this->retrieved_photo_ids))), 'Existing photos of the target album were hydrated.');
		self::assertSame([], array_values(array_intersect($album_ids, array_keys($this->retrieved_album_ids))), 'Child albums of the target album were hydrated.');
	}
}
