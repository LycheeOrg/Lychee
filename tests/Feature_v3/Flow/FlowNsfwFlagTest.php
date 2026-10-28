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

namespace Tests\Feature_v3\Flow;

use App\Models\Configs;
use App\Repositories\ConfigManager;
use Tests\Feature_v3\Base\BaseApiWithDataTest;

/**
 * Feature 103 (FR-103-12, S-103-28): Flow marks and hides albums below an NSFW
 * album through the stored `albums.is_recursive_nsfw`.
 */
class FlowNsfwFlagTest extends BaseApiWithDataTest
{
	private const KEYS = ['hide_nsfw_in_flow', 'flow_include_sub_albums', 'flow_base'];

	/** @var array<string,string> */
	private array $initial_values = [];

	public function setUp(): void
	{
		parent::setUp();
		config(['features.struct-of-array' => true]);
		foreach (self::KEYS as $key) {
			$this->initial_values[$key] = strval(Configs::query()->where('key', '=', $key)->value('value'));
		}
		Configs::set('flow_include_sub_albums', '1');
		Configs::set('flow_base', '');

		$this->assertCreated($this->actingAs($this->admin)->postJson('Album::updateProtectionPolicy', [
			'album_id' => $this->album1->id,
			'is_public' => false,
			'is_link_required' => false,
			'is_nsfw' => true,
			'grants_download' => false,
			'grants_upload' => false,
			'grants_full_photo_access' => false,
		]));
	}

	public function tearDown(): void
	{
		foreach ($this->initial_values as $key => $value) {
			Configs::query()->where('key', '=', $key)->update(['value' => $value]);
		}
		resolve(ConfigManager::class)->invalidateCache();
		parent::tearDown();
	}

	public function testItemsBelowAnNsfwAlbumAreMarked(): void
	{
		Configs::set('hide_nsfw_in_flow', '0');

		$response = $this->actingAs($this->userMayUpload1)->getJsonV3('Flow');
		$this->assertOk($response);
		$is_nsfw_by_id = array_combine($response->json('ids'), $response->json('is_nsfws'));
		self::assertTrue($is_nsfw_by_id[$this->subAlbum1->id]);
		self::assertFalse($is_nsfw_by_id[$this->album4->id]);

		$response = $this->actingAs($this->userMayUpload1)->getJson('Flow');
		$this->assertOk($response);
		$items = collect($response->json('albums'))->keyBy('id');
		self::assertTrue($items[$this->subAlbum1->id]['is_nsfw']);
	}

	public function testItemsBelowAnNsfwAlbumAreHidden(): void
	{
		Configs::set('hide_nsfw_in_flow', '1');

		$response = $this->actingAs($this->userMayUpload1)->getJsonV3('Flow');
		$this->assertOk($response);
		self::assertNotContains($this->album1->id, $response->json('ids'));
		self::assertNotContains($this->subAlbum1->id, $response->json('ids'));
		self::assertContains($this->album4->id, $response->json('ids'));
	}
}
