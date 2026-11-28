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

use App\Actions\Statistics\Spaces;
use App\Enum\SizeVariantType;
use App\Enum\SmartAlbumType;
use App\Enum\StorageDiskType;
use App\Models\Configs;
use App\Models\Photo;
use App\Models\SizeVariant;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Testing\TestResponse;
use Tests\Feature_v3\Base\BaseApiWithDataTest;

/**
 * Feature 119 (FR-119-03, S-119-06, S-119-15, S-119-16): a trashed photo is
 * hidden from its owner and from admins too.
 *
 * Each case checks that the photo is listed, trashes it the way a delete
 * does (every album link removed, a `trashed_photos` row), then checks that
 * it is gone.
 */
class TrashVisibilityTest extends BaseApiWithDataTest
{
	public function testUnsortedHidesATrashedPhotoFromItsOwnerAndFromAdmins(): void
	{
		foreach ([$this->userMayUpload1, $this->admin] as $user) {
			$this->assertListedThenHidden(
				$this->photoUnsorted,
				fn () => $this->actingAs($user)->getJsonV3('Albums/' . SmartAlbumType::UNSORTED->value . '/Photos'),
			);
			DB::table('trashed_photos')->delete();
		}
	}

	public function testRecentHidesATrashedPhoto(): void
	{
		$this->assertListedThenHidden(
			$this->photo1,
			fn () => $this->actingAs($this->userMayUpload1)->getJsonV3('Albums/' . SmartAlbumType::RECENT->value . '/Photos'),
		);
	}

	public function testTagAlbumHidesATrashedPhoto(): void
	{
		$this->assertListedThenHidden(
			$this->photo1,
			fn () => $this->actingAs($this->userMayUpload1)->getJsonV3('Albums/' . $this->tagAlbum1->id . '/Photos'),
		);
	}

	public function testSearchHidesATrashedPhoto(): void
	{
		DB::table('photos')->where('id', '=', $this->photo1->id)->update(['title' => 'Trashvisibility']);

		$this->assertListedThenHidden(
			$this->photo1,
			fn () => $this->actingAs($this->userMayUpload1)->getJsonV3('Search/Photos', ['terms' => base64_encode('Trashvisibility')]),
		);
	}

	public function testMapHidesATrashedPhoto(): void
	{
		Configs::set('map_display', '1');
		Configs::set('map_display_public', '1');

		$this->assertListedThenHidden(
			$this->photoUnsorted,
			fn () => $this->actingAs($this->userMayUpload1)->getJsonV3('Map/Photos', ['north' => 90.0, 'south' => -90.0, 'east' => 180.0, 'west' => -180.0, 'zoom' => 1]),
		);
	}

	public function testAssetOfATrashedPhotoIsNotFound(): void
	{
		Storage::fake(StorageDiskType::LOCAL->value);
		$thumb = SizeVariant::query()->where('photo_id', '=', $this->photo1->id)->where('type', '=', SizeVariantType::THUMB)->firstOrFail();
		Storage::disk(StorageDiskType::LOCAL->value)->put($thumb->short_path, 'thumb-bytes');
		$uri = 'Asset/' . $this->album1->id . '/' . $this->photo1->id . '/thumb';
		$before = $this->actingAs($this->userMayUpload1)->getV3($uri);
		$before->assertOk();

		$this->trash($this->photo1);

		$after = $this->actingAs($this->userMayUpload1)->getV3($uri);
		self::assertSame(404, $after->getStatusCode());
	}

	/**
	 * S-119-15: the owner's space statistics no longer count a trashed photo.
	 * (The upload quota reads album sizes only, which drop when the album's
	 * size is recomputed after the delete.).
	 */
	public function testOwnerSpaceNoLongerCountsATrashedPhoto(): void
	{
		$before = $this->spaceOf($this->userMayUpload1);
		$size = intval(DB::table('size_variants')
			->where('photo_id', '=', $this->photoUnsorted->id)
			->where('type', '!=', SizeVariantType::PLACEHOLDER->value)
			->sum('filesize'));
		self::assertGreaterThan(0, $size);

		$this->trash($this->photoUnsorted);

		self::assertSame($before - $size, $this->spaceOf($this->userMayUpload1));
	}

	/**
	 * S-119-16: the duplicate lookup of an upload (`FindDuplicate`, a model
	 * query) does not find a trashed photo, so the file is stored as a new photo.
	 */
	public function testDuplicateLookupIgnoresATrashedPhoto(): void
	{
		$checksum = $this->photoUnsorted->checksum;
		self::assertNotNull($this->findDuplicate($checksum));

		$this->trash($this->photoUnsorted);

		self::assertNull($this->findDuplicate($checksum));
	}

	/**
	 * @param \Closure(): TestResponse $request
	 */
	private function assertListedThenHidden(Photo $photo, \Closure $request): void
	{
		$before = $request();
		$this->assertOk($before);
		self::assertStringContainsString($photo->id, $before->getContent(), 'The photo should be listed before it is trashed.');

		$this->trash($photo);

		$after = $request();
		$this->assertOk($after);
		self::assertStringNotContainsString($photo->id, $after->getContent(), 'A trashed photo is listed.');
	}

	private function trash(Photo $photo): void
	{
		DB::table('photo_album')->where('photo_id', '=', $photo->id)->delete();
		DB::table('trashed_photos')->insert(['photo_id' => $photo->id, 'trashed_at' => now()]);
	}

	private function spaceOf(User $user): int
	{
		return intval((new Spaces())->getSpacePerSizeVariantTypePerUser($user->id)->sum('size'));
	}

	private function findDuplicate(string $checksum): ?Photo
	{
		return Photo::query()
			->where('checksum', '=', $checksum)
			->orWhere('original_checksum', '=', $checksum)
			->orWhere('live_photo_checksum', '=', $checksum)
			->first();
	}
}
