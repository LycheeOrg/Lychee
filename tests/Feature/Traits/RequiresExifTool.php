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

namespace Tests\Feature\Traits;

use App\Models\Configs;

trait RequiresExifTool
{
	protected bool $hasExifTools;
	protected int $hasImagickInit;

	protected function setUpRequiresExifTool(): void
	{
		$this->hasImagickInit = (int) Configs::get_value(self::CONFIG_HAS_EXIF_TOOL, 2);
		Configs::set(self::CONFIG_HAS_EXIF_TOOL, 2);
		$this->hasExifTools = Configs::hasExiftool();
	}

	protected function tearDownRequiresExifTool(): void
	{
		Configs::set(self::CONFIG_HAS_EXIF_TOOL, $this->hasImagickInit);
	}

	protected function assertHasExifToolOrSkip(): void
	{
		if (!$this->hasExifTools) {
			static::markTestSkipped('Exiftool is not available. Test Skipped.');
		}
	}

	abstract public static function markTestSkipped(string $message = ''): void;
}