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

use App\DTO\SortingCriterion;
use App\Facades\AccessControl;
use App\Http\Requests\Settings\SetSortingRequest;
use Tests\TestCase;

class SettingsTest extends TestCase
{
	public function testSetSorting(): void
	{
		AccessControl::log_as_id(0);

		$this->postJson('/api/Settings::setSorting',
		[
			SetSortingRequest::ALBUM_SORTING_COLUMN_ATTRIBUTE => SortingCriterion::COLUMN_CREATED_AT,
			SetSortingRequest::PHOTO_SORTING_COLUMN_ATTRIBUTE => SortingCriterion::COLUMN_CREATED_AT,
			SetSortingRequest::ALBUM_SORTING_ORDER_ATTRIBUTE => SortingCriterion::ASC,
			SetSortingRequest::PHOTO_SORTING_ORDER_ATTRIBUTE => SortingCriterion::ASC,
		])->assertStatus(204);

		AccessControl::logout();
	}

	public function testSetSortingWithIllegalAlbumAttribute(): void
	{
		AccessControl::log_as_id(0);

		$response = $this->postJson('/api/Settings::setSorting',
			[
				SetSortingRequest::ALBUM_SORTING_COLUMN_ATTRIBUTE => '123',
				SetSortingRequest::PHOTO_SORTING_COLUMN_ATTRIBUTE => SortingCriterion::COLUMN_CREATED_AT,
				SetSortingRequest::ALBUM_SORTING_ORDER_ATTRIBUTE => SortingCriterion::ASC,
				SetSortingRequest::PHOTO_SORTING_ORDER_ATTRIBUTE => SortingCriterion::ASC,
			]);

		$response->assertStatus(422);
		$response->assertSee('sorting albums column must be null or one out of');

		AccessControl::logout();
	}

	public function testSetSortingWithIllegalPhotoAttribute(): void
	{
		AccessControl::log_as_id(0);

		$response = $this->postJson('/api/Settings::setSorting',
			[
				SetSortingRequest::ALBUM_SORTING_COLUMN_ATTRIBUTE => SortingCriterion::COLUMN_CREATED_AT,
				SetSortingRequest::PHOTO_SORTING_COLUMN_ATTRIBUTE => '123',
				SetSortingRequest::ALBUM_SORTING_ORDER_ATTRIBUTE => SortingCriterion::ASC,
				SetSortingRequest::PHOTO_SORTING_ORDER_ATTRIBUTE => SortingCriterion::ASC,
			]);

		$response->assertStatus(422);
		$response->assertSee('sorting photos column must be null or one out of');

		AccessControl::logout();
	}

	public function testSetSortingWithUnknownOrder(): void
	{
		AccessControl::log_as_id(0);

		$response = $this->postJson('/api/Settings::setSorting',
			[
				SetSortingRequest::ALBUM_SORTING_COLUMN_ATTRIBUTE => SortingCriterion::COLUMN_CREATED_AT,
				SetSortingRequest::PHOTO_SORTING_COLUMN_ATTRIBUTE => SortingCriterion::COLUMN_CREATED_AT,
				SetSortingRequest::ALBUM_SORTING_ORDER_ATTRIBUTE => '123',
				SetSortingRequest::PHOTO_SORTING_ORDER_ATTRIBUTE => '123',
			]);

		$response->assertStatus(422);
		$response->assertSee('order must be either');

		AccessControl::logout();
	}
}
