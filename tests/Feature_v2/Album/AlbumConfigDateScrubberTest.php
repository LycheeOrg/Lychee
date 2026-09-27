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

namespace Tests\Feature_v2\Album;

use App\Models\Configs;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Feature_v2\Base\BaseApiWithDataTest;

/**
 * Feature 071 (FR-071-03/04/05): the date scrubber fields of `AlbumConfig`,
 * as returned by `Album::head`.
 */
class AlbumConfigDateScrubberTest extends BaseApiWithDataTest
{
	/** @var array<string,string> */
	private array $saved_configs = [];

	public function tearDown(): void
	{
		try {
			foreach ($this->saved_configs as $key => $value) {
				Configs::set($key, $value);
			}
		} finally {
			$this->saved_configs = [];
			parent::tearDown();
		}
	}

	private function setConfig(string $key, string $value): void
	{
		if (!array_key_exists($key, $this->saved_configs)) {
			$this->saved_configs[$key] = (string) Configs::query()->where('key', '=', $key)->value('value');
		}
		Configs::set($key, $value);
	}

	/**
	 * @return array<string,mixed>
	 */
	private function headConfig(string $album_id): array
	{
		$response = $this->actingAs($this->admin)->getJsonWithData('Album::head', ['album_id' => $album_id]);
		$this->assertOk($response);

		return $response->json('config');
	}

	/**
	 * @return array<string,array{0:string,1:?bool,2:bool}>
	 */
	public static function enabledProvider(): array
	{
		return [
			'global on, no override' => ['1', null, true],
			'global off, no override' => ['0', null, false],
			'global off, override on' => ['0', true, true],
			'global on, override off' => ['1', false, false],
		];
	}

	#[DataProvider('enabledProvider')]
	public function testIsDateScrubberEnabled(string $global, ?bool $override, bool $expected): void
	{
		$this->setConfig('album_date_scrubber_enabled', $global);
		DB::table('base_albums')->where('id', '=', $this->album1->id)->update(['is_date_scrubber_enabled' => $override]);

		self::assertSame($expected, $this->headConfig($this->album1->id)['is_date_scrubber_enabled']);
	}

	/**
	 * @return array<string,array{0:string,1:string,2:?string}>
	 */
	public static function photoFieldProvider(): array
	{
		return [
			'taken_at' => ['taken_at', 'date_prefix', 'taken_at'],
			'created_at' => ['created_at', 'date_prefix', 'created_at'],
			'title, date prefix mode' => ['title', 'date_prefix', 'title'],
			'title, alphabetical mode' => ['title', 'alphabetical', null],
			'rating' => ['rating_avg', 'date_prefix', null],
			'type' => ['type', 'date_prefix', null],
			'highlighted' => ['is_highlighted', 'date_prefix', null],
		];
	}

	#[DataProvider('photoFieldProvider')]
	public function testPhotoDateScrubberField(string $column, string $title_mode, ?string $expected): void
	{
		$this->setConfig('photo_title_bucket_mode', $title_mode);
		DB::table('base_albums')->where('id', '=', $this->album1->id)->update(['sorting_col' => $column, 'sorting_order' => 'DESC']);

		self::assertSame($expected, $this->headConfig($this->album1->id)['photo_date_scrubber_field']);
	}

	/**
	 * @return array<string,array{0:string,1:string,2:?string}>
	 */
	public static function albumFieldProvider(): array
	{
		return [
			'created_at' => ['created_at', 'date_prefix', 'created_at'],
			'min_taken_at' => ['min_taken_at', 'date_prefix', 'min_taken_at'],
			'max_taken_at' => ['max_taken_at', 'date_prefix', 'max_taken_at'],
			'title, date prefix mode' => ['title', 'date_prefix', 'title'],
			'title, alphabetical mode' => ['title', 'alphabetical', null],
		];
	}

	#[DataProvider('albumFieldProvider')]
	public function testAlbumDateScrubberField(string $column, string $title_mode, ?string $expected): void
	{
		$this->setConfig('title_bucket_mode', $title_mode);
		DB::table('albums')->where('id', '=', $this->album1->id)->update(['album_sorting_col' => $column, 'album_sorting_order' => 'DESC']);

		self::assertSame($expected, $this->headConfig($this->album1->id)['album_date_scrubber_field']);
	}

	public function testPhotoFieldFallsBackToGlobalSorting(): void
	{
		$this->setConfig('sorting_photos_col', 'taken_at');
		DB::table('base_albums')->where('id', '=', $this->album1->id)->update(['sorting_col' => null, 'sorting_order' => null]);

		self::assertSame('taken_at', $this->headConfig($this->album1->id)['photo_date_scrubber_field']);
	}

	public function testTagAlbumHasNoAlbumField(): void
	{
		$config = $this->headConfig($this->tagAlbum1->id);

		self::assertNull($config['album_date_scrubber_field']);
	}

	public function testSmartAlbumFollowsGlobalSettingOnly(): void
	{
		$this->setConfig('album_date_scrubber_enabled', '0');
		$this->setConfig('sorting_photos_col', 'created_at');

		$config = $this->headConfig('unsorted');

		self::assertFalse($config['is_date_scrubber_enabled']);
		self::assertSame('created_at', $config['photo_date_scrubber_field']);
		self::assertNull($config['album_date_scrubber_field']);
	}

	public function testLabelFormatComesFromTimelinePhotoDayFormat(): void
	{
		$this->setConfig('timeline_photo_date_format_day', 'Y/m/d');

		self::assertSame('Y/m/d', $this->headConfig($this->album1->id)['date_scrubber_label_format']);
	}
}
