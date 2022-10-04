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

class CommandGenerateThumbsTest extends PhotoTestBase
{
	public const COMMAND = 'lychee:generate_thumbs';

	public function testNoArguments(): void
	{
		$this->expectExceptionMessage('Not enough arguments (missing: "type").');
		$this->artisan(self::COMMAND)->run();
	}

	public function testInvalidSizeVariantArgument(): void
	{
		$this->artisan(self::COMMAND, ['type' => 'smally'])
			->expectsOutput('Type smally is not one of thumb, thumb2x, small, small2x, medium, medium2x')
			->assertExitCode(1);
	}

	public function testNoSizeVariantsMissing(): void
	{
		$this->artisan(self::COMMAND, ['type' => 'small'])
			->expectsOutput('No picture requires small.')
			->assertExitCode(0);
	}

	public function testThumbRecreation(): void
	{
		$photo1 = static::convertJsonToObject($this->photos_tests->upload(
			static::createUploadedFile(TestCase::SAMPLE_FILE_NIGHT_IMAGE)
		));

		// Remove the size variant "small" from disk and from DB
		\Safe\unlink(public_path($photo1->size_variants->small->url));
		DB::table('size_variants')
			->where('photo_id', '=', $photo1->id)
			->where('type', '=', SizeVariant::SMALL)
			->delete();

		// Re-create it
		$this->artisan(self::COMMAND, ['type' => 'small'])
			->assertExitCode(0);

		// Get updated photo and check if size variant has been re-created
		$photo2 = static::convertJsonToObject($this->photos_tests->get($photo1->id));
		static::assertNotNull($photo2->size_variants->small);
		static::assertEquals($photo1->size_variants->small->width, $photo2->size_variants->small->width);
		static::assertEquals($photo1->size_variants->small->height, $photo2->size_variants->small->height);
		static::assertFileExists(public_path($photo2->size_variants->small->url));
	}
}
