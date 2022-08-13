<?php

namespace Tests\Feature\Base;

use App\SmartAlbums\PublicAlbum;
use App\SmartAlbums\RecentAlbum;
use App\SmartAlbums\StarredAlbum;
use App\SmartAlbums\UnsortedAlbum;

abstract class SharingWithNonAdminUserAbstract extends SharingTestScenariosAbstract
{
	protected function generateExpectedRootJson(
		?string $unsortedAlbumThumbID = null,
		?string $starredAlbumThumbID = null,
		?string $publicAlbumThumbID = null,
		?string $recentAlbumThumbID = null,
		array $expectedSharedAlbumJson = []
	): array {
		return [
			'smart_albums' => [
				UnsortedAlbum::ID => ['thumb' => $this->generateExpectedThumbJson($unsortedAlbumThumbID)],
				StarredAlbum::ID => ['thumb' => $this->generateExpectedThumbJson($starredAlbumThumbID)],
				PublicAlbum::ID => ['thumb' => $this->generateExpectedThumbJson($publicAlbumThumbID)],
				RecentAlbum::ID => ['thumb' => $this->generateExpectedThumbJson($recentAlbumThumbID)],
			],
			'tag_albums' => [],
			'albums' => [],
			'shared_albums' => $expectedSharedAlbumJson,
		];
	}

	protected function generateExpectedSmartAlbumJson(
		bool $isPublic,
		?string $thumbID = null,
		array $expectedPhotos = []
	): array {
		return [
			'is_public' => $isPublic,
			'thumb' => $this->generateExpectedThumbJson($thumbID),
			'photos' => $expectedPhotos,
		];
	}
}
