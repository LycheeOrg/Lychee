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

namespace Tests\Feature_v3\Album;

use Illuminate\Support\Facades\DB;
use Tests\Feature_v3\Base\BaseApiWithDataTest;

/**
 * Feature 105: `PATCH /Album` stores every album sort column the
 * validation accepts (FR-105-01, S-105-01 … S-105-03).
 */
class AlbumSortingColumnTest extends BaseApiWithDataTest
{
	/**
	 * @return array<string,mixed>
	 */
	private function albumPayload(?string $column, ?string $order): array
	{
		return [
			'album_id' => $this->album1->id,
			'title' => 'title',
			'license' => 'none',
			'description' => '',
			'photo_sorting_column' => null,
			'photo_sorting_order' => null,
			'album_sorting_column' => $column,
			'album_sorting_order' => $order,
			'album_aspect_ratio' => null,
			'photo_layout' => null,
			'copyright' => '',
			'is_compact' => false,
			'is_map_header' => false,
			'is_pinned' => false,
			'header_id' => null,
			'cover_id' => null,
			'album_timeline' => null,
			'photo_timeline' => null,
		];
	}

	/**
	 * @return array{0:string|null,1:string|null}
	 */
	private function storedSorting(): array
	{
		$row = DB::table('albums')->where('id', '=', $this->album1->id)->first(['album_sorting_col', 'album_sorting_order']);

		return [$row->album_sorting_col, $row->album_sorting_order];
	}

	public function testMaxTakenAtIsStored(): void
	{
		$response = $this->actingAs($this->userMayUpload1)->patchJson('Album', $this->albumPayload('max_taken_at', 'DESC'));
		$this->assertOk($response);
		$response->assertJson(['album_sorting' => ['column' => 'max_taken_at', 'order' => 'DESC']]);

		self::assertSame(['max_taken_at', 'DESC'], $this->storedSorting());
	}

	public function testMinTakenAtIsStored(): void
	{
		$response = $this->actingAs($this->userMayUpload1)->patchJson('Album', $this->albumPayload('min_taken_at', 'ASC'));
		$this->assertOk($response);
		$response->assertJson(['album_sorting' => ['column' => 'min_taken_at', 'order' => 'ASC']]);

		self::assertSame(['min_taken_at', 'ASC'], $this->storedSorting());
	}

	public function testNullClearsSorting(): void
	{
		DB::table('albums')->where('id', '=', $this->album1->id)->update(['album_sorting_col' => 'title', 'album_sorting_order' => 'ASC']);

		$response = $this->actingAs($this->userMayUpload1)->patchJson('Album', $this->albumPayload(null, null));
		$this->assertOk($response);
		$response->assertJson(['album_sorting' => null]);

		self::assertSame([null, null], $this->storedSorting());
	}
}
