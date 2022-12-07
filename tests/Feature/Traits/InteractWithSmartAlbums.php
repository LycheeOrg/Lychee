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

use App\SmartAlbums\OnThisDayAlbum;
use App\SmartAlbums\PublicAlbum;
use App\SmartAlbums\RecentAlbum;
use App\SmartAlbums\StarredAlbum;
use App\SmartAlbums\UnsortedAlbum;

/**
 * Destroys the singleton of each smart album.
 *
 * This trait is necessary during tests, because the sequence of all tests
 * runs in the same process and hence the singletons are not regenerated
 * between different tests unless we explicitly enforce it.
 */
trait InteractWithSmartAlbums
{
	protected function clearCachedSmartAlbums(): void
	{
		foreach ([
			PublicAlbum::class,
			RecentAlbum::class,
			StarredAlbum::class,
			UnsortedAlbum::class,
			OnThisDayAlbum::class,
		] as $smartAlbumClass) {
			$reflection = new \ReflectionClass($smartAlbumClass);
			$reflection->setStaticPropertyValue('instance', null);
		}
	}
}
