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

use App\DTO\BaseSortingCriterion;
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
			SetSortingRequest::ALBUM_SORTING_COLUMN_ATTRIBUTE => BaseSortingCriterion::COLUMN_CREATED_AT,
			SetSortingRequest::PHOTO_SORTING_COLUMN_ATTRIBUTE => BaseSortingCriterion::COLUMN_CREATED_AT,
			SetSortingRequest::ALBUM_SORTING_ORDER_ATTRIBUTE => BaseSortingCriterion::ASC,
			SetSortingRequest::PHOTO_SORTING_ORDER_ATTRIBUTE => BaseSortingCriterion::ASC,
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
				SetSortingRequest::PHOTO_SORTING_COLUMN_ATTRIBUTE => BaseSortingCriterion::COLUMN_CREATED_AT,
				SetSortingRequest::ALBUM_SORTING_ORDER_ATTRIBUTE => BaseSortingCriterion::ASC,
				SetSortingRequest::PHOTO_SORTING_ORDER_ATTRIBUTE => BaseSortingCriterion::ASC,
			]);

		$this->assertStatus($response, 422);
		$response->assertSee('sorting albums column must be null or one out of');

		Auth::logout();
		Session::flush();
	}

	public function testSetSortingWithIllegalPhotoAttribute(): void
	{
		Auth::loginUsingId(0);

		$response = $this->postJson('/api/Settings::setSorting',
			[
				SetSortingRequest::ALBUM_SORTING_COLUMN_ATTRIBUTE => BaseSortingCriterion::COLUMN_CREATED_AT,
				SetSortingRequest::PHOTO_SORTING_COLUMN_ATTRIBUTE => '123',
				SetSortingRequest::ALBUM_SORTING_ORDER_ATTRIBUTE => BaseSortingCriterion::ASC,
				SetSortingRequest::PHOTO_SORTING_ORDER_ATTRIBUTE => BaseSortingCriterion::ASC,
			]);

		$this->assertStatus($response, 422);
		$response->assertSee('sorting photos column must be null or one out of');

		Auth::logout();
		Session::flush();
	}

	public function testSetSortingWithUnknownOrder(): void
	{
		Auth::loginUsingId(0);

		$response = $this->postJson('/api/Settings::setSorting',
			[
				SetSortingRequest::ALBUM_SORTING_COLUMN_ATTRIBUTE => BaseSortingCriterion::COLUMN_CREATED_AT,
				SetSortingRequest::PHOTO_SORTING_COLUMN_ATTRIBUTE => BaseSortingCriterion::COLUMN_CREATED_AT,
				SetSortingRequest::ALBUM_SORTING_ORDER_ATTRIBUTE => '123',
				SetSortingRequest::PHOTO_SORTING_ORDER_ATTRIBUTE => '123',
			]);

		$this->assertStatus($response, 422);
		$response->assertSee('order must be either');

		Auth::logout();
		Session::flush();
	}
}
