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

namespace Tests\Feature_v3\Photo;

use App\Models\Album;
use App\Models\Configs;
use App\Models\Photo;
use App\Repositories\ConfigManager;
use Illuminate\Support\Facades\Config;
use Tests\Feature_v3\Base\BaseApiWithDataTest;

/**
 * Feature 082 (FR-082-04): the manual 360° flag on `PATCH /Photo`.
 */
class Photo360EditTest extends BaseApiWithDataTest
{
	private const CACHE_CONFIGS = ['managed_cache_enabled', 'managed_cache_albums_enabled'];

	private Album $album;
	private Photo $photo;
	/** @var array<string,string> */
	private array $cache_configs = [];

	public function setUp(): void
	{
		parent::setUp();
		// Configs::set() is not rolled back with the test transaction: restore them in tearDown().
		$config_manager = resolve(ConfigManager::class);
		foreach (self::CACHE_CONFIGS as $key) {
			$this->cache_configs[$key] = $config_manager->getValueAsString($key);
		}
		$this->album = Album::factory()->as_root()->owned_by($this->userMayUpload1)->create();
		$this->photo = Photo::factory()->owned_by($this->userMayUpload1)->in($this->album)->create(['is_360' => false]);
	}

	public function tearDown(): void
	{
		foreach ($this->cache_configs as $key => $value) {
			Configs::set($key, $value);
		}
		parent::tearDown();
	}

	public function testSetAndClearFlag(): void
	{
		$this->assertOk($this->edit($this->photo, ['is_360' => true]));
		self::assertTrue($this->photo->fresh()->is_360);

		$this->assertOk($this->edit($this->photo, ['is_360' => false]));
		self::assertFalse($this->photo->fresh()->is_360);
	}

	public function testManualFlagKeepsCrop(): void
	{
		$this->photo->forceFill(['pano_full_width' => 800, 'pano_full_height' => 400, 'pano_crop_left' => 100, 'pano_crop_top' => 50])->save();

		$response = $this->edit($this->photo, ['is_360' => true]);
		$this->assertOk($response);

		$response->assertJsonPath('precomputed.is_360', true);
		$response->assertJsonPath('panorama.full_width', 800);
		$response->assertJsonPath('panorama.crop_top', 50);
	}

	public function testRequestWithoutFlagLeavesItUnchanged(): void
	{
		$this->photo->forceFill(['is_360' => true])->save();

		$this->assertOk($this->edit($this->photo, []));
		self::assertTrue($this->photo->fresh()->is_360);
	}

	public function testNonBooleanFlagIsRejected(): void
	{
		$this->assertUnprocessable($this->edit($this->photo, ['is_360' => 'yes']));
	}

	public function testVideoCannotBeFlagged(): void
	{
		$video = Photo::factory()->owned_by($this->userMayUpload1)->in($this->album)->create(['type' => 'video/mp4', 'is_360' => false]);

		$this->assertUnprocessable($this->edit($video, ['is_360' => true]));
		self::assertFalse($video->fresh()->is_360);

		$this->assertOk($this->edit($video, ['is_360' => false]));
	}

	public function testChangeInvalidatesCachedListing(): void
	{
		Config::set('features.struct-of-array', true);
		Config::set('features.enable-caching', true);
		Configs::set('managed_cache_enabled', '1');
		Configs::set('managed_cache_albums_enabled', '1');

		$before = $this->actingAs($this->userMayUpload1)->getJsonV3("Albums/{$this->album->id}/Photos");
		$this->assertOk($before);
		self::assertSame([false], $before->json('is_360s'));

		$this->assertOk($this->edit($this->photo, ['is_360' => true]));

		$after = $this->actingAs($this->userMayUpload1)->getJsonV3("Albums/{$this->album->id}/Photos");
		$this->assertOk($after);
		self::assertSame([true], $after->json('is_360s'));
	}

	/**
	 * @param array<string,mixed> $extra
	 */
	private function edit(Photo $photo, array $extra)
	{
		return $this->actingAs($this->userMayUpload1)->patchJson('Photo', [
			'photo_id' => $photo->id,
			'title' => $photo->title,
			'description' => '',
			'tags' => [],
			'license' => 'none',
			'taken_at' => null,
			'upload_date' => '2021-01-01',
			'from_id' => $this->album->id,
			...$extra,
		]);
	}
}
