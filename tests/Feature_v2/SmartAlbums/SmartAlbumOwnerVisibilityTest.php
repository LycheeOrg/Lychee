<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

/**
 * We don't care for unhandled exceptions in tests.
 * It is the nature of a test to throw an exception.
 * Without this suppression we had 100+ Linter warning in this file which
 * don't help anything.
 *
 * @noinspection PhpDocMissingThrowsInspection
 * @noinspection PhpUnhandledExceptionInspection
 */

namespace Tests\Feature_v2\SmartAlbums;

use App\Enum\SmartAlbumType;
use App\Factories\AlbumFactory;
use App\Models\User;
use App\Policies\AlbumPolicy;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Tests\Feature_v2\Base\BaseApiWithDataTest;

/**
 * Feature 118: the smart albums that need upload rights are also shown to
 * users who own at least one photo (FR-118-16, NFR-118-03, S-118-11).
 */
class SmartAlbumOwnerVisibilityTest extends BaseApiWithDataTest
{
	/**
	 * @return SmartAlbumType[]
	 */
	private function uploadGatedTypes(): array
	{
		return array_values(array_filter(SmartAlbumType::cases(), fn (SmartAlbumType $type) => $type->require_upload_rights()));
	}

	private function canSee(User $user, SmartAlbumType $type): bool
	{
		$smart_album = resolve(AlbumFactory::class)->createSmartAlbum($type, false);

		return Gate::forUser($user)->check(AlbumPolicy::CAN_SEE, $smart_album);
	}

	public function testPhotoOwnerWithoutUploadSeesUploadGatedSmartAlbums(): void
	{
		// `userNoUpload` owns photo3.
		self::assertNotEmpty($this->uploadGatedTypes());
		foreach ($this->uploadGatedTypes() as $type) {
			self::assertTrue($this->canSee($this->userNoUpload, $type), $type->value);
		}

		$response = $this->actingAs($this->userNoUpload)->getJsonWithData('Album::head', ['album_id' => SmartAlbumType::RECENT->value]);
		$this->assertOk($response);
	}

	public function testUserOwningNoPhotoDoesNotSeeThem(): void
	{
		$viewer = User::factory()->create();
		foreach ($this->uploadGatedTypes() as $type) {
			self::assertFalse($this->canSee($viewer, $type), $type->value);
		}

		$response = $this->actingAs($viewer)->getJsonWithData('Album::head', ['album_id' => SmartAlbumType::RECENT->value]);
		$this->assertForbidden($response);
	}

	public function testOwnershipIsCheckedOncePerRequest(): void
	{
		DB::enableQueryLog();
		foreach ($this->uploadGatedTypes() as $type) {
			$this->canSee($this->userNoUpload, $type);
		}
		$ownership_queries = array_filter(
			DB::getQueryLog(),
			fn (array $q) => preg_match('/from\s+photos\b.*owner_id/i', str_replace(['"', '`'], '', $q['query'])) === 1
		);
		DB::disableQueryLog();

		self::assertCount(1, $ownership_queries);
	}
}
