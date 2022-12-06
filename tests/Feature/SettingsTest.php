<?php

/**
 * We don't care for unhandled exceptions in tests.
 * It is the nature of a test to throw an exception.
 * Without this suppression we had 100+ Linter warning in this file which
 * don't help anything.
 *
 * @noinspection PhpDocMissingThrowsInspection
 * @noinspection PhpUnhandledExceptionInspection
 */

namespace Tests\Feature;

use App\Enum\ColumnSortingType;
use App\Enum\OrderSortingType;
use App\Http\Requests\Settings\SetSortingRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Tests\TestCase;

class SettingsTest extends TestCase
{
	public function testSetSorting(): void
	{
		Auth::loginUsingId(0);

		$this->postJson('/api/Settings::setSorting', [
			SetSortingRequest::ALBUM_SORTING_COLUMN_ATTRIBUTE => ColumnSortingType::CREATED_AT->value,
			SetSortingRequest::PHOTO_SORTING_COLUMN_ATTRIBUTE => ColumnSortingType::CREATED_AT->value,
			SetSortingRequest::ALBUM_SORTING_ORDER_ATTRIBUTE => OrderSortingType::ASC->value,
			SetSortingRequest::PHOTO_SORTING_ORDER_ATTRIBUTE => OrderSortingType::ASC->value,
		])->assertStatus(204);

		Auth::logout();
		Session::flush();
	}

	public function testSetSortingWithIllegalAlbumAttribute(): void
	{
		Auth::loginUsingId(0);

		$response = $this->postJson('/api/Settings::setSorting',
			[
				SetSortingRequest::ALBUM_SORTING_COLUMN_ATTRIBUTE => '123',
				SetSortingRequest::PHOTO_SORTING_COLUMN_ATTRIBUTE => ColumnSortingType::CREATED_AT->value,
				SetSortingRequest::ALBUM_SORTING_ORDER_ATTRIBUTE => OrderSortingType::ASC->value,
				SetSortingRequest::PHOTO_SORTING_ORDER_ATTRIBUTE => OrderSortingType::ASC->value,
			]);

		$this->assertStatus($response, 422);
		$response->assertSee('The selected sorting albums column is invalid');

		Auth::logout();
		Session::flush();
	}

	public function testSetSortingWithIllegalPhotoAttribute(): void
	{
		Auth::loginUsingId(0);

		$response = $this->postJson('/api/Settings::setSorting',
			[
				SetSortingRequest::ALBUM_SORTING_COLUMN_ATTRIBUTE => ColumnSortingType::CREATED_AT->value,
				SetSortingRequest::PHOTO_SORTING_COLUMN_ATTRIBUTE => '123',
				SetSortingRequest::ALBUM_SORTING_ORDER_ATTRIBUTE => OrderSortingType::ASC->value,
				SetSortingRequest::PHOTO_SORTING_ORDER_ATTRIBUTE => OrderSortingType::ASC->value,
			]);

		$this->assertStatus($response, 422);
		$response->assertSee('The selected sorting photos column is invalid');

		Auth::logout();
		Session::flush();
	}

	public function testSetSortingWithUnknownOrder(): void
	{
		Auth::loginUsingId(0);

		$response = $this->postJson('/api/Settings::setSorting',
			[
				SetSortingRequest::ALBUM_SORTING_COLUMN_ATTRIBUTE => ColumnSortingType::CREATED_AT->value,
				SetSortingRequest::PHOTO_SORTING_COLUMN_ATTRIBUTE => ColumnSortingType::CREATED_AT->value,
				SetSortingRequest::ALBUM_SORTING_ORDER_ATTRIBUTE => '123',
				SetSortingRequest::PHOTO_SORTING_ORDER_ATTRIBUTE => '123',
			]);

		$this->assertStatus($response, 422);
		$response->assertSee('The selected sorting photos order is invalid');

		Auth::logout();
		Session::flush();
	}
}
