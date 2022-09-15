<?php

/**
 * We don't care for unhandled exceptions in tests.
 * It is the nature of a test to throw an exception.
 * Without this suppression we had 100+ Linter warning in this file which
 * don't help anything.
 *
 * @noinspection PhpDocMissingThrowsInspection
 * @noinspection PhpUnhandledExceptionInspection
 */

namespace Tests\Feature;

use App\Models\SizeVariant;
use Illuminate\Support\Facades\DB;
use Tests\Feature\Base\PhotoTestBase;
use Tests\TestCase;

class CommandVideoDataTest extends PhotoTestBase
{
	public const COMMAND = 'lychee:video_data';

	public function testThumbRecreation(): void
	{
		$this->assertHasFFMpegOrSkip();

		$photo1 = static::convertJsonToObject($this->photos_tests->upload(
			static::createUploadedFile(TestCase::SAMPLE_FILE_TRAIN_VIDEO)
		));

		// Remove the size variant "thumb" from disk and from DB
		\Safe\unlink(public_path($photo1->size_variants->thumb->url));
		DB::table('size_variants')
			->where('photo_id', '=', $photo1->id)
			->where('type', '=', SizeVariant::THUMB)
			->delete();

		// Re-create it
		$this->artisan(self::COMMAND)
			->assertExitCode(0);

		// Get updated video and check if thumb has been re-created
		$photo2 = static::convertJsonToObject($this->photos_tests->get($photo1->id));
		static::assertNotNull($photo2->size_variants->thumb);
		static::assertEquals($photo1->size_variants->thumb->width, $photo2->size_variants->thumb->width);
		static::assertEquals($photo1->size_variants->thumb->height, $photo2->size_variants->thumb->height);
		static::assertFileExists(public_path($photo2->size_variants->thumb->url));
	}
}
