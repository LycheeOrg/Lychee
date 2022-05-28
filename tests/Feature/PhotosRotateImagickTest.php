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
 * Runs the tests of {@link PhotosRotateTestAbstract} with Imagick as image handler.
 */
class PhotosRotateImagickTest extends PhotosRotateTestAbstract
{
	protected int $hasImagickInit;

	public function setUp(): void
	{
		parent::setUp();

		$this->hasImagickInit = (int) Configs::get_value(self::CONFIG_HAS_IMAGICK, 1);
		Configs::set(self::CONFIG_HAS_IMAGICK, 1);

		if (!Configs::hasImagick()) {
			static::markTestSkipped('Imagick is not available. Test Skipped.');
		}
	}

	public function tearDown(): void
	{
		Configs::set(self::CONFIG_HAS_IMAGICK, $this->hasImagickInit);
		parent::tearDown();
	}
}
