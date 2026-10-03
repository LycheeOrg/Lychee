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
use App\Models\Photo;
use Illuminate\Support\Facades\Config;
use Tests\Feature_v3\Base\BaseApiWithDataTest;

/**
 * Feature 081 (FR-081-06): the 360° flag and the partial-panorama crop in
 * the v2 photo payload and the v3 Struct-of-Arrays tiers.
 */
class Photo360ResourcesTest extends BaseApiWithDataTest
{
	private const TERM = 'P360_';

	private const PANORAMA = ['full_width' => 800, 'full_height' => 400, 'crop_left' => 100, 'crop_top' => 50];

	private Album $album;
	private Photo $full;
	private Photo $partial;
	private Photo $flat;
	private Photo $unchecked;

	public function setUp(): void
	{
		parent::setUp();
		Config::set('features.struct-of-array', true);

		$this->album = Album::factory()->as_root()->owned_by($this->userMayUpload1)->create();
		$this->full = $this->photo('full', ['is_360' => true]);
		$this->partial = $this->photo('partial', [
			'is_360' => true,
			'pano_full_width' => 800,
			'pano_full_height' => 400,
			'pano_crop_left' => 100,
			'pano_crop_top' => 50,
		]);
		$this->flat = $this->photo('flat', ['is_360' => false]);
		$this->unchecked = $this->photo('unchecked', ['is_360' => null]);
	}

	public function testV2PhotoPayload(): void
	{
		$response = $this->actingAs($this->userMayUpload1)->getJsonWithData('Album::photos', ['album_id' => $this->album->id]);
		$this->assertOk($response);
		$photos = collect($response->json('photos'))->keyBy('id');

		self::assertTrue($photos[$this->full->id]['precomputed']['is_360']);
		self::assertNull($photos[$this->full->id]['panorama']);
		self::assertTrue($photos[$this->partial->id]['precomputed']['is_360']);
		self::assertSame(self::PANORAMA, $photos[$this->partial->id]['panorama']);
		self::assertFalse($photos[$this->flat->id]['precomputed']['is_360']);
		self::assertNull($photos[$this->flat->id]['panorama']);
		self::assertFalse($photos[$this->unchecked->id]['precomputed']['is_360']);
	}

	public function testV3Ratios(): void
	{
		$response = $this->actingAs($this->userMayUpload1)->getJsonV3("Albums/{$this->album->id}/Photos");
		$this->assertOk($response);

		$this->assertFlags(array_combine($response->json('ids'), $response->json('is_360s')));
	}

	public function testV3Details(): void
	{
		$response = $this->actingAs($this->userMayUpload1)->getJsonV3("Albums/{$this->album->id}/Photos/details", [
			'photo_ids' => $this->ids(),
		]);
		$this->assertOk($response);

		$this->assertPanoramas(array_combine($response->json('ids'), $response->json('panoramas')));
	}

	public function testV3Search(): void
	{
		$response = $this->actingAs($this->userMayUpload1)->getJsonV3('Search/Photos', ['terms' => base64_encode(self::TERM)]);
		$this->assertOk($response);

		$this->assertFlags(array_combine($response->json('ids'), $response->json('is_360s')));
	}

	public function testV3SearchDetails(): void
	{
		$response = $this->actingAs($this->userMayUpload1)->getJsonV3('Search/Photos/details', [
			'terms' => base64_encode(self::TERM),
			'photo_ids' => $this->ids(),
		]);
		$this->assertOk($response);

		$this->assertPanoramas(array_combine($response->json('ids'), $response->json('panoramas')));
	}

	/**
	 * @param array<string,mixed> $attributes
	 */
	private function photo(string $name, array $attributes): Photo
	{
		return Photo::factory()->owned_by($this->userMayUpload1)->in($this->album)->create(['title' => self::TERM . $name, ...$attributes]);
	}

	/**
	 * @return string[]
	 */
	private function ids(): array
	{
		return [$this->full->id, $this->partial->id, $this->flat->id, $this->unchecked->id];
	}

	/**
	 * @param array<string,bool> $flags
	 */
	private function assertFlags(array $flags): void
	{
		self::assertTrue($flags[$this->full->id]);
		self::assertTrue($flags[$this->partial->id]);
		self::assertFalse($flags[$this->flat->id]);
		self::assertFalse($flags[$this->unchecked->id]);
	}

	/**
	 * @param array<string,array<string,int>|null> $panoramas
	 */
	private function assertPanoramas(array $panoramas): void
	{
		self::assertNull($panoramas[$this->full->id]);
		self::assertSame(self::PANORAMA, $panoramas[$this->partial->id]);
		self::assertNull($panoramas[$this->flat->id]);
		self::assertNull($panoramas[$this->unchecked->id]);
	}
}
