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

use App\Models\Configs;

/**
 * Runs the tests of {@link PhotosRotateTestAbstract} with GD as image handler.
 */
class PhotosRotateGDTest extends PhotosRotateTestAbstract
{
	protected int $hasImagickInit;

	public function setUp(): void
	{
		parent::setUp();

		$this->hasImagickInit = (int) Configs::get_value(self::CONFIG_HAS_IMAGICK, 0);
		Configs::set(self::CONFIG_HAS_IMAGICK, 0);

		if (Configs::hasImagick()) {
			static::markTestSkipped('Imagick still enabled although it shouldn\'t. Test Skipped.');
		}
	}

	public function tearDown(): void
	{
		Configs::set(self::CONFIG_HAS_IMAGICK, $this->hasImagickInit);
		parent::tearDown();
	}
}
