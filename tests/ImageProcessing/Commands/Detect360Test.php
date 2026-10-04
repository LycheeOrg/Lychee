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

use App\Events\PhotoSaved;
use App\Models\Album;
use App\Models\Photo;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Tests\Constants\TestConstants;
use Tests\Feature_v2\Base\BaseApiWithDataTest;

/**
 * Feature 082 (FR-082-05): `lychee:detect_360` flags photos never checked.
 */
class Detect360Test extends BaseApiWithDataTest
{
	private Album $album;

	public function setUp(): void
	{
		parent::setUp();
		// The shared fixture photos have no file on disk: mark them checked
		// so that only the photos of this test are candidates.
		DB::table('photos')->update(['is_360' => false]);
		$this->album = Album::factory()->as_root()->owned_by($this->admin)->create();
	}

	public function testNothingToDo(): void
	{
		$this->artisan('lychee:detect_360')
			->expectsOutput('No photos require 360° detection.')
			->assertExitCode(0);
	}

	public function testDetectsPhotosNeverChecked(): void
	{
		$full = $this->upload360(TestConstants::SAMPLE_FILE_PHOTOSPHERE);
		$partial = $this->upload360(TestConstants::SAMPLE_FILE_PHOTOSPHERE_PARTIAL);
		$plain = $this->upload360(TestConstants::SAMPLE_FILE_HOCHUFERWEG);
		$manual = $this->upload360(TestConstants::SAMPLE_FILE_AARHUS);
		$video = Photo::factory()->owned_by($this->admin)->in($this->album)->create(['type' => 'video/mp4', 'is_360' => null]);

		$this->uncheck($full, $partial, $plain);
		$manual->forceFill(['is_360' => true])->save();
		Event::fake([PhotoSaved::class]);

		$this->artisan('lychee:detect_360')
			->expectsOutputToContain($full->id)
			->expectsOutputToContain($partial->id)
			->expectsOutput('Checked 3 photos: 2 360° photos, 0 failed.')
			->assertExitCode(0);

		self::assertTrue($full->fresh()->is_360);
		self::assertNull($full->fresh()->pano_full_width);
		$partial = $partial->fresh();
		self::assertTrue($partial->is_360);
		self::assertSame(800, $partial->pano_full_width);
		self::assertSame(100, $partial->pano_crop_top);
		self::assertFalse($plain->fresh()->is_360);
		self::assertTrue($manual->fresh()->is_360);
		self::assertNull($video->fresh()->is_360);
		Event::assertDispatched(PhotoSaved::class, fn (PhotoSaved $event) => $event->photo_ids === [$full->id, $partial->id] || $event->photo_ids === [$partial->id, $full->id]);

		$this->artisan('lychee:detect_360')
			->expectsOutput('No photos require 360° detection.')
			->assertExitCode(0);
	}

	public function testUnreadableOriginalIsReported(): void
	{
		$broken = $this->upload360(TestConstants::SAMPLE_FILE_HOCHUFERWEG);
		$this->uncheck($broken);
		$broken->size_variants->getOriginal()->getFile()->delete();

		$this->artisan('lychee:detect_360')
			->expectsOutput('Checked 1 photos: 0 360° photos, 1 failed.')
			->assertExitCode(1);

		self::assertNull($broken->fresh()->is_360);
	}

	/**
	 * Batches continue after the last photo of the previous one: a photo
	 * checked (or failed) in a batch never shifts the next batch, and an
	 * unreadable original never blocks the photos after it.
	 */
	public function testCursorContinuesAfterThePreviousBatch(): void
	{
		$sphere = $this->upload360(TestConstants::SAMPLE_FILE_PHOTOSPHERE);
		$photos = [$sphere, $this->upload360(TestConstants::SAMPLE_FILE_HOCHUFERWEG)];
		$this->uncheck(...$photos);
		// Order as the database does: its collation of ids is not byte order on MySQL/PostgreSQL.
		[$first, $second] = Photo::query()->with('size_variants')->whereIn('id', array_map(fn (Photo $p) => $p->id, $photos))->orderBy('id')->get()->all();
		$first->size_variants->getOriginal()->getFile()->delete();

		$this->artisan('lychee:detect_360', ['limit' => 1])
			->expectsOutput('Checked 1 photos: 0 360° photos, 1 failed.')
			->expectsOutput('More photos may remain: continue with --after=' . $first->id)
			->assertExitCode(1);
		self::assertNull($first->fresh()->is_360);
		self::assertNull($second->fresh()->is_360);

		$this->artisan('lychee:detect_360', ['limit' => 1, '--after' => $first->id])
			->expectsOutput('Checked 1 photos: ' . ($second->id === $sphere->id ? 1 : 0) . ' 360° photos, 0 failed.')
			->expectsOutput('More photos may remain: continue with --after=' . $second->id)
			->assertExitCode(0);
		self::assertNotNull($second->fresh()->is_360);

		$this->artisan('lychee:detect_360', ['limit' => 1, '--after' => $second->id])
			->expectsOutput('No photos require 360° detection.')
			->assertExitCode(0);
	}

	private function upload360(string $filename): Photo
	{
		$before = DB::table('photo_album')->where('album_id', '=', $this->album->id)->pluck('photo_id')->all();
		$response = $this->actingAs($this->admin)->upload('Photo', filename: $filename, album_id: $this->album->id);
		$this->assertCreated($response);

		$id = DB::table('photo_album')->where('album_id', '=', $this->album->id)->whereNotIn('photo_id', $before)->sole(['photo_id'])->photo_id;

		return Photo::query()->findOrFail($id);
	}

	private function uncheck(Photo ...$photos): void
	{
		foreach ($photos as $photo) {
			$photo->forceFill(['is_360' => null, 'pano_full_width' => null, 'pano_full_height' => null, 'pano_crop_left' => null, 'pano_crop_top' => null])->save();
		}
	}
}
