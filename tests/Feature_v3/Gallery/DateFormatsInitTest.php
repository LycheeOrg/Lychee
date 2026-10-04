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

namespace Tests\Feature_v3\Gallery;

use App\Models\Configs;
use App\Repositories\ConfigManager;
use Tests\Feature_v3\Base\BaseApiWithDataTest;

/**
 * Every date format the frontend applies client-side is served once, through
 * Gallery::Init, and nowhere else (FR-063-16, FR-065-21, FR-071).
 */
class DateFormatsInitTest extends BaseApiWithDataTest
{
	private const KEYS = [
		'date_format_album_thumb',
		'thumb_min_max_order',
		'date_format_photo_overlay',
		'date_format_sidebar_uploaded',
		'date_format_sidebar_taken_at',
		'timeline_photo_date_format_day',
	];

	/** @var array<string,string> */
	private array $initial_values = [];

	public function setUp(): void
	{
		parent::setUp();
		foreach (self::KEYS as $key) {
			$this->initial_values[$key] = strval(Configs::query()->where('key', '=', $key)->value('value'));
		}
	}

	public function tearDown(): void
	{
		foreach ($this->initial_values as $key => $value) {
			Configs::query()->where('key', '=', $key)->update(['value' => $value]);
		}
		resolve(ConfigManager::class)->invalidateCache();
		parent::tearDown();
	}

	public function testDefaults(): void
	{
		$response = $this->getJson('Gallery::Init');
		$this->assertOk($response);
		$response->assertJson([
			'date_format_album_thumb' => 'M Y',
			'thumb_min_max_order' => 'younger_older',
			'date_format_photo_overlay' => 'M j, Y, g:i:s A e',
			'date_format_sidebar_uploaded' => 'M j, Y, g:i:s A e',
			'date_format_sidebar_taken_at' => 'M j, Y, g:i:s A e',
			'date_scrubber_label_format' => $this->initial_values['timeline_photo_date_format_day'],
		]);
	}

	public function testUpdatedValues(): void
	{
		Configs::set('date_format_album_thumb', 'F Y');
		Configs::set('thumb_min_max_order', 'older_younger');
		Configs::set('date_format_photo_overlay', 'd/m/Y');
		Configs::set('date_format_sidebar_uploaded', 'Y-m-d H:i');
		Configs::set('date_format_sidebar_taken_at', 'j F Y');
		Configs::set('timeline_photo_date_format_day', 'Y/m/d');
		resolve(ConfigManager::class)->invalidateCache();

		$response = $this->getJson('Gallery::Init');
		$this->assertOk($response);
		$response->assertJson([
			'date_format_album_thumb' => 'F Y',
			'thumb_min_max_order' => 'older_younger',
			'date_format_photo_overlay' => 'd/m/Y',
			'date_format_sidebar_uploaded' => 'Y-m-d H:i',
			'date_format_sidebar_taken_at' => 'j F Y',
			'date_scrubber_label_format' => 'Y/m/d',
		]);
	}

	public function testAlbumAndRootConfigsCarryNoDateFormat(): void
	{
		$head = $this->actingAs($this->userMayUpload1)->getJsonWithData('Album::head', ['album_id' => $this->album1->id]);
		$this->assertOk($head);
		$root = $this->actingAs($this->userMayUpload1)->getJson('Albums');
		$this->assertOk($root);

		foreach (['date_format_album_thumb', 'thumb_min_max_order', 'date_scrubber_label_format'] as $field) {
			self::assertArrayNotHasKey($field, $head->json('config'));
			self::assertArrayNotHasKey($field, $root->json('config'));
		}
	}
}
