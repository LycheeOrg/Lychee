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

use App\Facades\AccessControl;
use App\Models\Configs;
use Illuminate\Support\Facades\DB;
use Tests\Feature\Lib\PhotosUnitTest;
use Tests\TestCase;

class PhotosRotateTest extends TestCase
{
	public const CONFIG_EDITOR_ENABLED = 'editor_enabled';

	protected PhotosUnitTest $photos_tests;
	protected int $editor_enabled_init;

	public function setUp(): void
	{
		parent::setUp();
		$this->photos_tests = new PhotosUnitTest($this);

		$this->editor_enabled_init = (int) Configs::get_value(self::CONFIG_EDITOR_ENABLED, 0);
		Configs::set(self::CONFIG_EDITOR_ENABLED, 1);

		AccessControl::log_as_id(0);

		// Assert that photo table is empty
		static::assertDatabaseCount('sym_links', 0);
		static::assertDatabaseCount('size_variants', 0);
		static::assertDatabaseCount('photos', 0);
	}

	public function tearDown(): void
	{
		// Clean up remaining stuff from tests
		DB::table('sym_links')->delete();
		DB::table('size_variants')->delete();
		DB::table('photos')->delete();
		self::cleanPublicFolders();

		Configs::set(self::CONFIG_EDITOR_ENABLED, $this->editor_enabled_init);

		AccessControl::logout();

		parent::tearDown();
	}

	public function testDisabledEditor(): void
	{
		Configs::set(self::CONFIG_EDITOR_ENABLED, 0);
		$id = $this->photos_tests->upload(
			TestCase::createUploadedFile(TestCase::SAMPLE_FILE_NIGHT_IMAGE)
		);

		$this->photos_tests->rotate($id, 1, 412, 'support for rotation disabled by configuration');
	}

	public function testInvalidValues(): void
	{
		$id = $this->photos_tests->upload(
			TestCase::createUploadedFile(TestCase::SAMPLE_FILE_NIGHT_IMAGE)
		);

		$this->photos_tests->rotate('-1', 1, 422);
		$this->photos_tests->rotate($id, 'asdq', 422, 'The selected direction is invalid');
		$this->photos_tests->rotate($id, '2', 422, 'The selected direction is invalid');
	}

	/**
	 * @return void
	 */
	public function testSimpleRotation(): void
	{
		$id = $this->photos_tests->upload(
			TestCase::createUploadedFile(TestCase::SAMPLE_FILE_NIGHT_IMAGE)
		);

		$response = $this->photos_tests->get($id);
		$response->assertJson([
			'id' => $id,
			'size_variants' => [
				'small' => ['width' => 540, 'height' => 360],
				'medium' => ['width' => 1620, 'height' => 1080],
				'original' => ['width' => 6720, 'height' => 4480],
			],
		]);

		$response = $this->photos_tests->rotate($id, 1);
		$response->assertJson([
			'id' => $id,
			'size_variants' => [
				'small' => ['width' => 240, 'height' => 360],
				'medium' => ['width' => 720, 'height' => 1080],
				'original' => ['width' => 4480, 'height' => 6720],
			],
		]);
	}
}
