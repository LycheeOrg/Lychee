<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
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

namespace Tests\Feature_v1;

use App\Enum\ColumnSortingType;
use App\Enum\OrderSortingType;
use App\Legacy\V1\Requests\Settings\SetSortingSettingsRequest;
use App\Models\Configs;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Session;
use Tests\AbstractTestCase;

class SettingsTest extends AbstractTestCase
{
	private function send(
		string $url,
		array $params,
		int $status = 204,
		?string $assertSee = null): void
	{
		Auth::loginUsingId(1);

		$response = $this->postJson('/api' . $url, $params);
		$this->assertStatus($response, $status);
		if ($assertSee !== null) {
			$response->assertSee($assertSee);
		}

		Auth::logout();
		Session::flush();
	}

	private function sendKV(
		string $url,
		string $key,
		string|bool|int $value,
		int $status = 204,
		?string $assertSee = null): void
	{
		$oldVal = Configs::getValue($key);
		Auth::loginUsingId(1);

		$response = $this->postJson('/api' . $url, [$key => $value]);
		$this->assertStatus($response, $status);
		if ($assertSee !== null) {
			$response->assertSee($assertSee);
		}

		Auth::logout();
		Session::flush();
		Configs::set($key, $oldVal);
	}

	// Route::post('/Settings::setSorting', [Administration\SettingsController::class, 'setSorting']);
	public function testSetSorting(): void
	{
		$this->send('/Settings::setSorting', [
			SetSortingSettingsRequest::ALBUM_SORTING_COLUMN_ATTRIBUTE => ColumnSortingType::CREATED_AT,
			SetSortingSettingsRequest::PHOTO_SORTING_COLUMN_ATTRIBUTE => ColumnSortingType::CREATED_AT,
			SetSortingSettingsRequest::ALBUM_SORTING_ORDER_ATTRIBUTE => OrderSortingType::ASC,
			SetSortingSettingsRequest::PHOTO_SORTING_ORDER_ATTRIBUTE => OrderSortingType::ASC,
		]);

		$this->send('/Settings::setSorting', [
			SetSortingSettingsRequest::ALBUM_SORTING_COLUMN_ATTRIBUTE => '123',
			SetSortingSettingsRequest::PHOTO_SORTING_COLUMN_ATTRIBUTE => ColumnSortingType::CREATED_AT,
			SetSortingSettingsRequest::ALBUM_SORTING_ORDER_ATTRIBUTE => OrderSortingType::ASC,
			SetSortingSettingsRequest::PHOTO_SORTING_ORDER_ATTRIBUTE => OrderSortingType::ASC,
		],
			422,
			'The selected sorting albums column is invalid'
		);

		$this->send('/Settings::setSorting', [
			SetSortingSettingsRequest::ALBUM_SORTING_COLUMN_ATTRIBUTE => ColumnSortingType::CREATED_AT,
			SetSortingSettingsRequest::PHOTO_SORTING_COLUMN_ATTRIBUTE => '123',
			SetSortingSettingsRequest::ALBUM_SORTING_ORDER_ATTRIBUTE => OrderSortingType::ASC,
			SetSortingSettingsRequest::PHOTO_SORTING_ORDER_ATTRIBUTE => OrderSortingType::ASC,
		],
			422,
			'The selected sorting photos column is invalid'
		);

		$this->send(
			'/Settings::setSorting',
			[
				SetSortingSettingsRequest::ALBUM_SORTING_COLUMN_ATTRIBUTE => ColumnSortingType::CREATED_AT,
				SetSortingSettingsRequest::PHOTO_SORTING_COLUMN_ATTRIBUTE => ColumnSortingType::CREATED_AT,
				SetSortingSettingsRequest::ALBUM_SORTING_ORDER_ATTRIBUTE => '123',
				SetSortingSettingsRequest::PHOTO_SORTING_ORDER_ATTRIBUTE => '123',
			],
			422,
			'The selected sorting photos order is invalid'
		);
	}

	// Route::post('/Settings::setLang', [Administration\SettingsController::class, 'setLang']);
	public function testSetLang(): void
	{
		$this->sendKV('/Settings::setLang', 'lang', 'wrong language', 422);
		$this->sendKV('/Settings::setLang', 'lang', 'en');
	}

	// Route::post('/Settings::setLayout', [Administration\SettingsController::class, 'setLayout']);
	public function testSetLayout(): void
	{
		$this->sendKV('/Settings::setLayout', 'layout', 3, 422);
		$this->sendKV('/Settings::setLayout', 'layout', 'something', 422);
		$this->sendKV('/Settings::setLayout', 'layout', 'justified');
	}

	// Route::post('/Settings::setDefaultLicense', [Administration\SettingsController::class, 'setDefaultLicense']);
	public function testSetDefaultLicense(): void
	{
		$this->send('/Settings::setDefaultLicense', ['license' => 'wrong'], 422);
		$this->send('/Settings::setDefaultLicense', ['license' => 'none']);
	}

	// Route::post('/Settings::setMapDisplay', [Administration\SettingsController::class, 'setMapDisplay']);
	public function testSetMapDisplaySetting(): void
	{
		$this->sendKV('/Settings::setMapDisplay', 'map_display', 'wrong', 422);
		$this->sendKV('/Settings::setMapDisplay', 'map_display', false);
	}

	// Route::post('/Settings::setMapDisplayPublic', [Administration\SettingsController::class, 'setMapDisplayPublic']);
	public function testSetMapDisplayPublic(): void
	{
		$this->sendKV('/Settings::setMapDisplayPublic', 'map_display_public', 'wrong', 422);
		$this->sendKV('/Settings::setMapDisplayPublic', 'map_display_public', false);
	}

	// Route::post('/Settings::setMapProvider', [Administration\SettingsController::class, 'setMapProvider']);
	public function testSetMapProviderSetting(): void
	{
		$this->sendKV('/Settings::setMapProvider', 'map_provider', 'wrong', 422);
		$this->sendKV('/Settings::setMapProvider', 'map_provider', 'Wikimedia');
	}

	// Route::post('/Settings::setMapIncludeSubAlbums', [Administration\SettingsController::class, 'setMapIncludeSubAlbums']);
	public function testSetMapIncludeSubAlbums(): void
	{
		$this->sendKV('/Settings::setMapIncludeSubAlbums', 'map_include_subalbums', 'wrong', 422);
		$this->sendKV('/Settings::setMapIncludeSubAlbums', 'map_include_subalbums', false);
	}

	// Route::post('/Settings::setLocationDecoding', [Administration\SettingsController::class, 'setLocationDecoding']);
	public function testSetLocationDecoding(): void
	{
		$this->sendKV('/Settings::setLocationDecoding', 'location_decoding', 'wrong', 422);
		$this->sendKV('/Settings::setLocationDecoding', 'location_decoding', false);
	}

	// Route::post('/Settings::setLocationShow', [Administration\SettingsController::class, 'setLocationShow']);
	public function testSetLocationShow(): void
	{
		$this->sendKV('/Settings::setLocationShow', 'location_show', 'wrong', 422);
		$this->sendKV('/Settings::setLocationShow', 'location_show', false);
	}

	// Route::post('/Settings::setLocationShowPublic', [Administration\SettingsController::class, 'setLocationShowPublic']);
	public function testSetLocationShowPublic(): void
	{
		$this->sendKV('/Settings::setLocationShowPublic', 'location_show_public', 'wrong', 422);
		$this->sendKV('/Settings::setLocationShowPublic', 'location_show_public', false);
	}

	// Route::post('/Settings::setPublicSearch', [Administration\SettingsController::class, 'setPublicSearch']);
	public function testSetPublicSearch(): void
	{
		$this->sendKV('/Settings::setPublicSearch', 'search_public', 'wrong', 422);
		$this->sendKV('/Settings::setPublicSearch', 'search_public', false);
	}

	// Route::post('/Settings::setCSS', [Administration\SettingsController::class, 'setCSS']);
	public function testSetCSS(): void
	{
		$this->send('/Settings::setCSS', ['css' => 'test']);
		$css = File::get(base_path('public/dist/user.css'));
		self::assertEquals('test', $css);
		$this->send('/Settings::setCSS', ['css' => '']);
	}

	// Route::post('/Settings::getAll', [Administration\SettingsController::class, 'getAll']);
	// Route::post('/Settings::saveAll', [Administration\SettingsController::class, 'saveAll']);
	public function testAllSettings(): void
	{
		Auth::loginUsingId(1);

		$response = $this->postJson('/api/Settings::getAll', []);
		$this->assertStatus($response, 200);

		$response = $this->postJson('/api/Settings::saveAll', ['nsfw_visible' => '0']);
		$this->assertStatus($response, 204);
		Auth::logout();
		Session::flush();
	}

	// Route::post('/Settings::setOverlayType', [Administration\SettingsController::class, 'setImageOverlayType']);
	public function testSetImageOverlay(): void
	{
		$this->sendKV('/Settings::setOverlayType', 'image_overlay_type', 'wrong', 422);
		$this->sendKV('/Settings::setOverlayType', 'image_overlay_type', 'none');
	}

	// Route::post('/Settings::setNSFWVisible', [Administration\SettingsController::class, 'setNSFWVisible']);
	public function testSetNSFWVisible(): void
	{
		$this->sendKV('/Settings::setNSFWVisible', 'nsfw_visible', 'wrong', 422);
		$this->sendKV('/Settings::setNSFWVisible', 'nsfw_visible', true);
	}

	// Route::post('/Settings::setDropboxKey', [Administration\SettingsController::class, 'setDropboxKey']);
	public function testSetDropBoxKey(): void
	{
		$this->send('/Settings::setDropboxKey', ['key' => true], 422);
		$this->send('/Settings::setDropboxKey', ['key' => 'test_drop_box_key']);
	}

	// Route::post('/Settings::setNewPhotosNotification', [Administration\SettingsController::class, 'setNewPhotosNotification']);
	public function testSetNewPhotosNotification(): void
	{
		$this->sendKV('/Settings::setNewPhotosNotification', 'new_photos_notification', 'wrong', 422);
		$this->sendKV('/Settings::setNewPhotosNotification', 'new_photos_notification', false);
	}
}
