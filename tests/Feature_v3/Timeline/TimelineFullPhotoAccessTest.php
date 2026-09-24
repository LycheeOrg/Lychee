<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

/**
 * @noinspection PhpDocMissingThrowsInspection
 * @noinspection PhpUnhandledExceptionInspection
 */

namespace Tests\Feature_v3\Timeline;

use App\Models\Configs;
use Illuminate\Support\Facades\Config;
use Tests\Feature_v3\Base\BaseApiWithDataTest;

/**
 * GHSA-mwvg-6vm3-jqhx — a logged-in sharee whose share withholds full-photo
 * access must not receive `size_variants.original.url` from the Timeline,
 * even though the `grants_full_photo_access` *config* (the seed for new
 * shares) is ON.
 *
 * `userMayUpload2` sees `album1` (owned by `userMayUpload1`) through `perm1`
 * and `perm11`; both grants are withheld here.
 */
class TimelineFullPhotoAccessTest extends BaseApiWithDataTest
{
	public function setUp(): void
	{
		parent::setUp();
		Config::set('features.struct-of-array', true);
		Configs::set('grants_full_photo_access', '1');
		Configs::set('timeline_page_enabled', '1');
	}

	private function setShareGrant(bool $granted): void
	{
		foreach ([$this->perm1, $this->perm11] as $perm) {
			$perm->grants_full_photo_access = $granted;
			$perm->save();
		}
	}

	/**
	 * Returns `false` when the photo is absent, `null` when downgraded.
	 */
	private function v2OriginalUrl(string $photo_id): string|false|null
	{
		$response = $this->getJson('Timeline');
		$this->assertOk($response);

		foreach ($response->json('photos') as $photo) {
			if ($photo['id'] === $photo_id) {
				self::assertNotNull($photo['size_variants']['medium'], 'fixture photo must have a medium variant');

				return $photo['size_variants']['original']['url'] ?? null;
			}
		}

		return false;
	}

	private function v3OriginalUrl(string $photo_id): string|false|null
	{
		$response = $this->getJsonV3('Albums/timeline/Photos/details', ['photo_ids' => [$photo_id]]);
		$this->assertOk($response);

		$index = array_search($photo_id, $response->json('ids'), true);
		if ($index === false) {
			return false;
		}
		self::assertNotNull($response->json("size_variants.{$index}.medium"), 'fixture photo must have a medium variant');

		return $response->json("size_variants.{$index}.original.url");
	}

	public function testV2TimelineDeniesOriginalToShareeWithoutFullPhotoGrant(): void
	{
		$this->setShareGrant(false);
		$this->actingAs($this->userMayUpload2);

		$url = $this->v2OriginalUrl($this->photo1->id);
		self::assertNotFalse($url, 'shared photo must appear in the sharee timeline');
		self::assertNull($url);
	}

	public function testV2TimelineAllowsOriginalToShareeWithFullPhotoGrant(): void
	{
		$this->setShareGrant(true);
		$this->actingAs($this->userMayUpload2);

		$url = $this->v2OriginalUrl($this->photo1->id);
		self::assertNotFalse($url, 'shared photo must appear in the sharee timeline');
		self::assertNotNull($url);
	}

	public function testV3TimelineDeniesOriginalToShareeWithoutFullPhotoGrant(): void
	{
		$this->setShareGrant(false);
		$this->actingAs($this->userMayUpload2);

		$url = $this->v3OriginalUrl($this->photo1->id);
		self::assertNotFalse($url, 'shared photo must appear in the sharee timeline');
		self::assertNull($url);
	}

	public function testV3TimelineAllowsOriginalToShareeWithFullPhotoGrant(): void
	{
		$this->setShareGrant(true);
		$this->actingAs($this->userMayUpload2);

		$url = $this->v3OriginalUrl($this->photo1->id);
		self::assertNotFalse($url, 'shared photo must appear in the sharee timeline');
		self::assertNotNull($url);
	}
}
