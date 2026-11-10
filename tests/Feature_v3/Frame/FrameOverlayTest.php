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

namespace Tests\Feature_v3\Frame;

use App\Models\AccessPermission;
use App\Models\Album;
use App\Models\Configs;
use App\Models\Photo;
use App\Repositories\ConfigManager;
use Illuminate\Support\Carbon;
use Tests\Feature_v3\Base\BaseApiWithDataTest;

/**
 * Feature 088: `GET /api/v2/Frame` returns the overlay settings and the
 * photo information the privacy rules allow (FR-088-05 … FR-088-07, FR-088-10).
 */
class FrameOverlayTest extends BaseApiWithDataTest
{
	private const KEYS = [
		'mod_frame_enabled',
		'random_album_id',
		'mod_frame_include_subalbums',
		'mod_frame_clock_time_enabled',
		'mod_frame_clock_time_format',
		'mod_frame_clock_date_enabled',
		'mod_frame_clock_position',
		'mod_frame_clock_background_color',
		'mod_frame_clock_background_opacity',
		'mod_frame_clock_opacity',
		'mod_frame_info_title_enabled',
		'mod_frame_info_albums_enabled',
		'mod_frame_info_exif_enabled',
		'mod_frame_info_exif_exposure_enabled',
		'mod_frame_info_exif_camera_enabled',
		'mod_frame_info_exif_taken_at_enabled',
		'mod_frame_info_position',
		'mod_frame_info_background_color',
		'mod_frame_info_background_opacity',
		'mod_frame_info_opacity',
		'display_exif_data',
		'file_name_hidden',
		'date_format_photo_overlay',
	];

	/** @var array<string,string> */
	private array $initial_values = [];

	public function setUp(): void
	{
		parent::setUp();
		foreach (self::KEYS as $key) {
			$this->initial_values[$key] = strval(Configs::query()->where('key', '=', $key)->value('value'));
		}
		Configs::set('mod_frame_enabled', '1');
		Configs::set('random_album_id', '');
		Configs::set('display_exif_data', '1');
		Configs::set('file_name_hidden', '0');
		Configs::set('date_format_photo_overlay', 'Y-m-d H:i');
	}

	public function tearDown(): void
	{
		foreach ($this->initial_values as $key => $value) {
			Configs::query()->where('key', '=', $key)->update(['value' => $value]);
		}
		resolve(ConfigManager::class)->invalidateCache();
		parent::tearDown();
	}

	/**
	 * An album of userMayUpload1 holding one photo with a title and full EXIF data.
	 */
	private function createAlbumWithExifPhoto(): Album
	{
		$album = Album::factory()->as_root()->owned_by($this->userMayUpload1)->create();
		$photo = Photo::factory()->owned_by($this->userMayUpload1)->in($album)->create();
		$photo->title = 'Sunset over the bay';
		$photo->focal = '35 mm';
		$photo->aperture = 'f/2.8';
		$photo->shutter = '1/250 s';
		$photo->iso = '100';
		$photo->make = 'Fujifilm';
		$photo->model = 'X-T5';
		$photo->lens = 'XF35mmF1.4 R';
		$photo->taken_at = Carbon::create(2024, 8, 12, 19, 47, 0, 'UTC');
		$photo->save();

		return $album;
	}

	// ── Settings (S-088-01, S-088-02) ───────────────────────────────

	public function testDefaultSettingsAreReturned(): void
	{
		$response = $this->actingAs($this->userMayUpload1)->getJsonWithData('Frame', ['album_id' => $this->album1->id]);
		$this->assertOk($response);
		$response->assertJson([
			'clock' => [
				'is_time_enabled' => false,
				'time_format' => '24h',
				'is_date_enabled' => false,
				'position' => 'bottom_right',
				'background_color' => '#000000',
				'background_opacity' => 50,
				'opacity' => 100,
			],
			'info' => [
				'is_title_enabled' => false,
				'is_albums_enabled' => false,
				'is_exif_enabled' => false,
				'is_exif_exposure_enabled' => true,
				'is_exif_camera_enabled' => true,
				'is_exif_taken_at_enabled' => true,
				'position' => 'bottom_left',
				'background_color' => '#000000',
				'background_opacity' => 50,
				'opacity' => 100,
			],
		]);
	}

	public function testChangedSettingsAreReturned(): void
	{
		Configs::set('mod_frame_clock_time_enabled', '1');
		Configs::set('mod_frame_clock_time_format', '12h');
		Configs::set('mod_frame_clock_date_enabled', '1');
		Configs::set('mod_frame_clock_position', 'center');
		Configs::set('mod_frame_clock_background_color', '#1e293b');
		Configs::set('mod_frame_clock_background_opacity', '0');
		Configs::set('mod_frame_clock_opacity', '80');
		Configs::set('mod_frame_info_title_enabled', '1');
		Configs::set('mod_frame_info_albums_enabled', '1');
		Configs::set('mod_frame_info_exif_enabled', '1');
		Configs::set('mod_frame_info_exif_exposure_enabled', '0');
		Configs::set('mod_frame_info_exif_camera_enabled', '0');
		Configs::set('mod_frame_info_exif_taken_at_enabled', '0');
		Configs::set('mod_frame_info_position', 'top_right');
		Configs::set('mod_frame_info_background_color', '');
		Configs::set('mod_frame_info_background_opacity', '100');
		Configs::set('mod_frame_info_opacity', '25');

		$response = $this->actingAs($this->userMayUpload1)->getJsonWithData('Frame', ['album_id' => $this->album1->id]);
		$this->assertOk($response);
		$response->assertJson([
			'clock' => [
				'is_time_enabled' => true,
				'time_format' => '12h',
				'is_date_enabled' => true,
				'position' => 'center',
				'background_color' => '#1e293b',
				'background_opacity' => 0,
				'opacity' => 80,
			],
			'info' => [
				'is_title_enabled' => true,
				'is_albums_enabled' => true,
				'is_exif_enabled' => true,
				'is_exif_exposure_enabled' => false,
				'is_exif_camera_enabled' => false,
				'is_exif_taken_at_enabled' => false,
				'position' => 'top_right',
				'background_color' => '',
				'background_opacity' => 100,
				'opacity' => 25,
			],
		]);
	}

	// ── Photo information (S-088-07 … S-088-09) ─────────────────────

	public function testPhotoInformationIsReturnedWhateverTheSwitches(): void
	{
		$album = $this->createAlbumWithExifPhoto();

		$response = $this->actingAs($this->userMayUpload1)->getJsonWithData('Frame', ['album_id' => $album->id]);
		$this->assertOk($response);
		$response->assertJson([
			'photo' => [
				'title' => 'Sunset over the bay',
				'albums' => null,
				'focal' => '35 mm',
				'aperture' => 'f/2.8',
				'shutter' => '1/250 s',
				'iso' => 'ISO 100',
				'make' => 'Fujifilm',
				'model' => 'X-T5',
				'lens' => 'XF35mmF1.4 R',
				'taken_at' => '2024-08-12 19:47',
			],
		]);
	}

	public function testExifHiddenWhenDisplayExifDataIsOff(): void
	{
		Configs::set('display_exif_data', '0');
		$album = $this->createAlbumWithExifPhoto();

		$response = $this->actingAs($this->userMayUpload1)->getJsonWithData('Frame', ['album_id' => $album->id]);
		$this->assertOk($response);
		$response->assertJson([
			'photo' => [
				'title' => 'Sunset over the bay',
				'focal' => null,
				'aperture' => null,
				'shutter' => null,
				'iso' => null,
				'make' => null,
				'model' => null,
				'lens' => null,
				'taken_at' => '2024-08-12 19:47',
			],
		]);
	}

	public function testTitleHiddenForGuestsWhenFileNameHidden(): void
	{
		$this->photo4->title = 'Public photo';
		$this->photo4->save();
		Configs::set('file_name_hidden', '1');

		$response = $this->getJsonWithData('Frame', ['album_id' => $this->album4->id]);
		$this->assertOk($response);
		$response->assertJsonPath('photo.title', null);

		$response = $this->actingAs($this->userMayUpload1)->getJsonWithData('Frame', ['album_id' => $this->album4->id]);
		$this->assertOk($response);
		$response->assertJsonPath('photo.title', 'Public photo');
	}

	// ── Album line (S-088-11 … S-088-13) ────────────────────────────

	/**
	 * Leaves photo4 as the only photo a guest can find, in album4 (renamed
	 * "Mountains"), a public album "Alps" and a public album "Secret" that
	 * requires its link.
	 */
	private function placePhoto4InThreeAlbums(): void
	{
		$this->perm44->delete();
		$this->album4->title = 'Mountains';
		$this->album4->save();

		$alps = Album::factory()->as_root()->owned_by($this->userLocked)->with_title('Alps')->create();
		AccessPermission::factory()->public()->visible()->for_album($alps)->create();
		$secret = Album::factory()->as_root()->owned_by($this->userLocked)->with_title('Secret')->create();
		AccessPermission::factory()->public()->for_album($secret)->create();
		$this->photo4->albums()->attach([$alps->id, $secret->id]);
	}

	public function testFullyRandomFrameListsBrowsableAlbumsSortedByTitle(): void
	{
		$this->placePhoto4InThreeAlbums();

		$response = $this->getJson('Frame');
		$this->assertOk($response);
		$response->assertJsonPath('photo.albums', ['Alps', 'Mountains']);
	}

	public function testAlbumFrameWithoutSubAlbumModeHasNoAlbumLine(): void
	{
		$this->placePhoto4InThreeAlbums();
		Configs::set('mod_frame_include_subalbums', '0');

		$response = $this->getJsonWithData('Frame', ['album_id' => $this->album4->id]);
		$this->assertOk($response);
		$response->assertJsonPath('photo.albums', null);
	}
}
