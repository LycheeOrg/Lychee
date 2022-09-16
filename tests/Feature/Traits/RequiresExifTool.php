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
use Tests\TestCase;

trait RequiresExifTool
{
	protected bool $hasExifTools;
	protected int $hasExifToolInit;

	protected function setUpRequiresExifTool(): void
	{
		$this->hasExifToolInit = Configs::getValueAsInt(TestCase::CONFIG_HAS_EXIF_TOOL);
		Configs::set(TestCase::CONFIG_HAS_EXIF_TOOL, 2);
		$this->hasExifTools = Configs::hasExiftool();
	}

	protected function tearDownRequiresExifTool(): void
	{
		Configs::set(TestCase::CONFIG_HAS_EXIF_TOOL, $this->hasExifToolInit);
	}

	protected function assertHasExifToolOrSkip(): void
	{
		if (!$this->hasExifTools) {
			static::markTestSkipped('Exiftool is not available. Test Skipped.');
		}
	}

	abstract public static function markTestSkipped(string $message = ''): void;
}