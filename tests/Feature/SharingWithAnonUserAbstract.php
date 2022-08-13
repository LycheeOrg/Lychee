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

use App\SmartAlbums\PublicAlbum;
use App\SmartAlbums\RecentAlbum;
use App\SmartAlbums\StarredAlbum;
use App\SmartAlbums\UnsortedAlbum;

/**
 * Implements the tests of {@link SharingTestScenariosAbstract} for an
 * anonymous user whose results are independent of the setting for public
 * search.
 */
abstract class SharingWithAnonUserAbstract extends SharingTestScenariosAbstract
{
	protected function generateExpectedRootJson(
		?string $unsortedAlbumThumbID = null,
		?string $starredAlbumThumbID = null,
		?string $publicAlbumThumbID = null,
		?string $recentAlbumThumbID = null,
		array $expectedAlbumJson = []
	): array {
		if ($unsortedAlbumThumbID !== null) {
			throw new \InvalidArgumentException('$unsortedAlbumThumbID must be `null` for test with unauthenticated users');
		}
		if ($publicAlbumThumbID !== null) {
			throw new \InvalidArgumentException('$publicAlbumThumbID must be `null` for test with unauthenticated users');
		}

		return [
			'smart_albums' => [
				UnsortedAlbum::ID => null,
				StarredAlbum::ID => ['thumb' => $this->generateExpectedThumbJson($starredAlbumThumbID)],
				PublicAlbum::ID => null,
				RecentAlbum::ID => ['thumb' => $this->generateExpectedThumbJson($recentAlbumThumbID)],
			],
			'tag_albums' => [],
			'albums' => $expectedAlbumJson,
			'shared_albums' => [],
		];
	}

	protected function generateExpectedTreeJson(array $expectedAlbums = []): array
	{
		return [
			'albums' => $expectedAlbums,
			'shared_albums' => [],
		];
	}

	protected function performPostPreparatorySteps(): void
	{
		// This is a no-op for the anonymous user, because we do not need
		// to log in
	}

	protected function getExpectedInaccessibleHttpStatusCode(): int
	{
		return 401;
	}

	protected function getExpectedDefaultInaccessibleMessage(): string
	{
		return self::EXPECTED_UNAUTHENTICATED_MSG;
	}
}
