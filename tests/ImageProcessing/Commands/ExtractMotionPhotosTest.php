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

namespace Tests\ImageProcessing\Commands;

use App\Enum\StorageDiskType;
use App\Events\PhotoSaved;
use App\Models\Album;
use App\Models\Configs;
use App\Models\Photo;
use App\Repositories\ConfigManager;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Storage;
use Tests\Constants\TestConstants;
use Tests\Feature_v2\Base\BaseApiWithDataTest;
use Tests\Traits\ProbesStoredVideo;
use Tests\Traits\RequiresFFMpeg;

/**
 * Covers `lychee:extract_motion_photos` (Feature 100, FR-100-04, S-100-11 …
 * S-100-14).
 */
class ExtractMotionPhotosTest extends BaseApiWithDataTest
{
	use ProbesStoredVideo;
	use RequiresFFMpeg;

	private Album $album;

	public function setUp(): void
	{
		parent::setUp();
		// The shared fixture photos have no file on disk: take them out of
		// the candidate set (JPEGs without a video) so only the photos of
		// this test are scanned.
		DB::table('photos')->update(['type' => TestConstants::MIME_TYPE_IMG_PNG]);
		$this->album = Album::factory()->as_root()->owned_by($this->admin)->create();
	}

	public function testNothingToDo(): void
	{
		$this->artisan('lychee:extract_motion_photos')
			->expectsOutput('No photos require motion photo extraction.')
			->assertExitCode(0);
	}

	public function testExtractsMotionPhotosAndIsIdempotent(): void
	{
		$this->assertHasFFMpegOrSkip();
		$motion = $this->uploadAsStill(TestConstants::SAMPLE_FILE_GMP_V1_IMAGE);
		$plain = $this->uploadAsStill(TestConstants::SAMPLE_FILE_HOCHUFERWEG);
		Event::fake([PhotoSaved::class]);

		$this->artisan('lychee:extract_motion_photos')
			->expectsOutputToContain('Motion photo: ' . $motion->id)
			->expectsOutput('Checked 2 photos: 1 motion photos extracted, 0 failed.')
			->assertExitCode(0);

		$this->assertVideoNextToOriginal($motion->fresh());
		self::assertNull($plain->fresh()->live_photo_short_path);
		Event::assertDispatched(PhotoSaved::class, fn (PhotoSaved $event) => $event->photo_ids === [$motion->id]);

		$this->artisan('lychee:extract_motion_photos')
			->expectsOutput('Checked 1 photos: 0 motion photos extracted, 0 failed.')
			->assertExitCode(0);
	}

	public function testDryRunChangesNothing(): void
	{
		$motion = $this->uploadAsStill(TestConstants::SAMPLE_FILE_GMP_ULTRAHDR_IMAGE);

		$this->artisan('lychee:extract_motion_photos', ['--dry-run' => true])
			->expectsOutputToContain('Motion photo: ' . $motion->id)
			->expectsOutput('Checked 1 photos: 1 motion photos found (dry run), 0 failed.')
			->assertExitCode(0);

		self::assertNull($motion->fresh()->live_photo_short_path);
		self::assertFalse(Storage::disk(StorageDiskType::LOCAL->value)->exists($this->expectedVideoPath($motion)));
	}

	public function testAlbumOption(): void
	{
		$this->assertHasFFMpegOrSkip();
		$inside = $this->uploadAsStill(TestConstants::SAMPLE_FILE_GMP_V1_IMAGE);
		$other_album = Album::factory()->as_root()->owned_by($this->admin)->create();
		$outside = $this->uploadAsStill(TestConstants::SAMPLE_FILE_GMP_ULTRAHDR_IMAGE, $other_album);

		$this->artisan('lychee:extract_motion_photos', ['--album' => $this->album->id])
			->expectsOutput('Checked 1 photos: 1 motion photos extracted, 0 failed.')
			->assertExitCode(0);

		self::assertNotNull($inside->fresh()->live_photo_short_path);
		self::assertNull($outside->fresh()->live_photo_short_path);
	}

	public function testUnknownAlbum(): void
	{
		$this->artisan('lychee:extract_motion_photos', ['--album' => 'doesnotexist'])
			->expectsOutput('Album doesnotexist not found.')
			->assertExitCode(1);
	}

	public function testRequiresFFMpeg(): void
	{
		$motion = $this->uploadAsStill(TestConstants::SAMPLE_FILE_GMP_V1_IMAGE);
		Configs::set(TestConstants::CONFIG_HAS_FFMPEG, 0);

		$this->artisan('lychee:extract_motion_photos')
			->expectsOutput('FFmpeg is required to extract motion photo videos (or use --dry-run).')
			->assertExitCode(1);

		self::assertNull($motion->fresh()->live_photo_short_path);
	}

	public function testUnreadableOriginalIsReported(): void
	{
		$this->assertHasFFMpegOrSkip();
		$broken = $this->uploadAsStill(TestConstants::SAMPLE_FILE_GMP_V1_IMAGE);
		$broken->size_variants->getOriginal()->getFile()->delete();

		$this->artisan('lychee:extract_motion_photos')
			->expectsOutput('Failed: ' . $broken->id . ' (' . $broken->title . '): original file is missing')
			->expectsOutput('Checked 1 photos: 0 motion photos extracted, 1 failed.')
			->assertExitCode(1);

		self::assertNull($broken->fresh()->live_photo_short_path);
	}

	/**
	 * PR #4900 review: continuing with the printed cursor would skip a photo
	 * that failed in the batch, so the command says to run the batch again
	 * first.
	 */
	public function testCursorAfterAFailureAsksToRetryTheBatch(): void
	{
		$this->assertHasFFMpegOrSkip();
		$photos = [$this->uploadAsStill(TestConstants::SAMPLE_FILE_GMP_V1_IMAGE), $this->uploadAsStill(TestConstants::SAMPLE_FILE_GMP_ULTRAHDR_IMAGE)];
		// Order as the database does: its collation of ids is not byte order on MySQL/PostgreSQL.
		[$first, $second] = Photo::query()->with('size_variants')->whereIn('id', array_map(fn (Photo $p) => $p->id, $photos))->orderBy('id')->get()->all();
		$first->size_variants->getOriginal()->getFile()->delete();

		$this->artisan('lychee:extract_motion_photos', ['limit' => 1])
			->expectsOutput('Failed: ' . $first->id . ' (' . $first->title . '): original file is missing')
			->expectsOutput('More photos may remain: continue with --after=' . $first->id)
			->expectsOutput('1 photos failed in this batch and would be skipped by that cursor: run it again without --after to retry them first.')
			->assertExitCode(1);

		$this->artisan('lychee:extract_motion_photos', ['limit' => 1, '--after' => $first->id])
			->expectsOutput('Checked 1 photos: 1 motion photos extracted, 0 failed.')
			->expectsOutput('More photos may remain: continue with --after=' . $second->id)
			->doesntExpectOutputToContain('failed in this batch')
			->assertExitCode(0);
	}

	public function testCursorContinuesAfterThePreviousBatch(): void
	{
		$photos = [$this->uploadAsStill(TestConstants::SAMPLE_FILE_GMP_V1_IMAGE), $this->uploadAsStill(TestConstants::SAMPLE_FILE_HOCHUFERWEG)];
		// Order as the database does: its collation of ids is not byte order on MySQL/PostgreSQL.
		[$first, $second] = Photo::query()->whereIn('id', array_map(fn (Photo $p) => $p->id, $photos))->orderBy('id')->get()->all();

		$this->artisan('lychee:extract_motion_photos', ['limit' => 1, '--dry-run' => true])
			->expectsOutput('More photos may remain: continue with --after=' . $first->id)
			->assertExitCode(0);

		$this->artisan('lychee:extract_motion_photos', ['limit' => 1, '--after' => $first->id, '--dry-run' => true])
			->expectsOutput('More photos may remain: continue with --after=' . $second->id)
			->assertExitCode(0);

		$this->artisan('lychee:extract_motion_photos', ['limit' => 1, '--after' => $second->id, '--dry-run' => true])
			->expectsOutput('No photos require motion photo extraction.')
			->assertExitCode(0);
	}

	/**
	 * S-100-23: a motion photo stored before Q-100-09 kept its HEVC clip;
	 * `--reencode` extracts it again from the original as H.264.
	 */
	public function testReencodeTranscodesStoredHevcClips(): void
	{
		$this->assertHasFFMpegOrSkip();
		$motion = $this->uploadPhoto(TestConstants::SAMPLE_FILE_GMP_PIXEL_IMAGE);
		$this->storeHevcClip($motion);
		self::assertSame('hevc', $this->probeStoredVideo($motion->live_photo_short_path)->get('codec_name'));

		$this->artisan('lychee:extract_motion_photos', ['--reencode' => true])
			->expectsOutputToContain('Motion photo: ' . $motion->id)
			->expectsOutput('Checked 1 photos: 1 motion photos re-extracted, 0 failed.')
			->assertExitCode(0);

		$motion = $motion->fresh();
		self::assertSame($this->expectedVideoPath($motion), $motion->live_photo_short_path);
		$stream = $this->probeStoredVideo($motion->live_photo_short_path);
		self::assertSame('h264', $stream->get('codec_name'));
		self::assertSame(1080, $stream->get('width'));
		self::assertSame(1440, $stream->get('height'));
	}

	public function testReencodeDryRunChangesNothing(): void
	{
		$this->assertHasFFMpegOrSkip();
		$motion = $this->uploadPhoto(TestConstants::SAMPLE_FILE_GMP_PIXEL_IMAGE);
		$this->storeHevcClip($motion);

		$this->artisan('lychee:extract_motion_photos', ['--reencode' => true, '--dry-run' => true])
			->expectsOutputToContain('Motion photo: ' . $motion->id)
			->expectsOutput('Checked 1 photos: 1 motion photos found (dry run), 0 failed.')
			->assertExitCode(0);

		self::assertSame('hevc', $this->probeStoredVideo($motion->live_photo_short_path)->get('codec_name'));
	}

	public function testReencodeLeavesApplePairsAlone(): void
	{
		$this->assertHasFFMpegOrSkip();
		$apple = $this->uploadPhoto(TestConstants::SAMPLE_FILE_TRAIN_IMAGE);
		$response = $this->actingAs($this->admin)->upload('Photo', filename: TestConstants::SAMPLE_FILE_TRAIN_VIDEO, album_id: $this->album->id);
		$this->assertCreated($response);
		self::assertNotNull($apple->fresh()->live_photo_content_id);
		self::assertNotNull($apple->fresh()->live_photo_short_path);

		$this->artisan('lychee:extract_motion_photos', ['--reencode' => true])
			->expectsOutput('No photos require motion photo extraction.')
			->assertExitCode(0);
	}

	/**
	 * Uploads with FFmpeg disabled, so the file is stored as a plain still,
	 * the way photos were imported before Feature 100. FFmpeg is restored to
	 * the value the test started with.
	 */
	private function uploadAsStill(string $filename, ?Album $album = null): Photo
	{
		$album ??= $this->album;
		$has_ffmpeg = resolve(ConfigManager::class)->getValueAsString(TestConstants::CONFIG_HAS_FFMPEG);
		Configs::set(TestConstants::CONFIG_HAS_FFMPEG, 0);

		$before = DB::table('photo_album')->where('album_id', '=', $album->id)->pluck('photo_id')->all();
		$response = $this->actingAs($this->admin)->upload('Photo', filename: $filename, album_id: $album->id);
		$this->assertCreated($response);
		$id = DB::table('photo_album')->where('album_id', '=', $album->id)->whereNotIn('photo_id', $before)->sole(['photo_id'])->photo_id;

		Configs::set(TestConstants::CONFIG_HAS_FFMPEG, $has_ffmpeg);
		$photo = Photo::query()->with('size_variants')->findOrFail($id);
		self::assertNull($photo->live_photo_short_path);

		return $photo;
	}

	/**
	 * Uploads into `$album` (this test's album by default) and returns the
	 * new photo.
	 */
	private function uploadPhoto(string $filename, ?Album $album = null): Photo
	{
		$album ??= $this->album;
		$before = DB::table('photo_album')->where('album_id', '=', $album->id)->pluck('photo_id')->all();
		$response = $this->actingAs($this->admin)->upload('Photo', filename: $filename, album_id: $album->id);
		$this->assertCreated($response);
		$id = DB::table('photo_album')->where('album_id', '=', $album->id)->whereNotIn('photo_id', $before)->sole(['photo_id'])->photo_id;

		return Photo::query()->with('size_variants')->findOrFail($id);
	}

	/**
	 * Replaces the stored clip with the raw HEVC stream embedded in the
	 * original, as extraction did before Q-100-09.
	 */
	private function storeHevcClip(Photo $photo): void
	{
		$disk = Storage::disk(StorageDiskType::LOCAL->value);
		$original = $disk->get($photo->size_variants->getOriginal()->short_path);
		$disk->put($photo->live_photo_short_path, substr($original, -3725212));
	}

	private function expectedVideoPath(Photo $photo): string
	{
		$original = $photo->size_variants->getOriginal()->short_path;

		return pathinfo($original, PATHINFO_DIRNAME) . '/' . pathinfo($original, PATHINFO_FILENAME) . '.mov';
	}

	private function assertVideoNextToOriginal(Photo $photo): void
	{
		self::assertSame($this->expectedVideoPath($photo), $photo->live_photo_short_path);
		$header = Storage::disk(StorageDiskType::LOCAL->value)->get($photo->live_photo_short_path);
		self::assertSame('ftyp', substr($header, 4, 4));
	}
}
