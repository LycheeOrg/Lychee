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

use App\Enum\SizeVariantType;
use Illuminate\Support\Facades\DB;
use Tests\AbstractTestCase;
use Tests\Feature\Base\BasePhotoTest;
use Tests\Feature\Traits\ExecuteAsAdmin;

class CommandVideoDataTest extends BasePhotoTest
{
	use ExecuteAsAdmin;

	public const COMMAND = 'lychee:video_data';

	public function testThumbRecreation(): void
	{
		$this->assertHasFFMpegOrSkip();

		/** @var \App\Models\Photo $photo1 */
		$photo1 = static::convertJsonToObject($this->photos_tests->upload(
			static::createUploadedFile(AbstractTestCase::SAMPLE_FILE_TRAIN_VIDEO)
		));

		// Remove the size variant "thumb" from disk and from DB
		\Safe\unlink(public_path($photo1->size_variants->thumb->url));
		DB::table('size_variants')
			->where('photo_id', '=', $photo1->id)
			->where('type', '=', SizeVariantType::THUMB)
			->delete();

		// Re-create it
		$this->artisan(self::COMMAND)
			->assertExitCode(0);

		// Get updated video and check if thumb has been re-created
		/** @var \App\Models\Photo $photo2 */
		$photo2 = static::convertJsonToObject($this->photos_tests->get($photo1->id));
		$this->assertNotNull($photo2->size_variants->thumb);
		$this->assertEquals($photo1->size_variants->thumb->width, $photo2->size_variants->thumb->width);
		$this->assertEquals($photo1->size_variants->thumb->height, $photo2->size_variants->thumb->height);
		$this->assertFileExists(public_path($photo2->size_variants->thumb->url));
	}
}
