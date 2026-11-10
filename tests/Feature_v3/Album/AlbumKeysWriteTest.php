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

namespace Tests\Feature_v3\Album;

use App\DTO\AlbumSortingCriterion;
use App\Enum\ColumnSortingType;
use App\Enum\OrderSortingType;
use App\Enum\RenamerModeType;
use App\Events\AlbumSaved;
use App\Models\Album;
use App\Models\RenamerRule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Tests\Feature_v3\Base\BaseApiWithDataTest;

/**
 * Feature 092 (FR-092-12, S-092-19, Q-092-04): a title change through the
 * bulk renamer recomputes the album's own `bucket_key` and flushes its
 * parent's children listing, like `PATCH /Album` does.
 */
class AlbumKeysWriteTest extends BaseApiWithDataTest
{
	public function setUp(): void
	{
		parent::setUp();
		config(['queue.default' => 'sync']);
		$this->requireSe();
		\Configs::set('renamer_enabled', true);
		DB::table('configs')->where('key', '=', 'title_bucket_mode')->update(['value' => 'date_prefix']);

		RenamerRule::factory()
			->owner_id($this->userMayUpload1->id)
			->order(1)
			->rule('date_rule')
			->description('Date rule')
			->needle('OLD ')
			->replacement('2024-03 ')
			->mode(RenamerModeType::FIRST)
			->state([
				'is_enabled' => true,
				'is_photo_rule' => false,
				'is_album_rule' => true,
			])
			->create();
	}

	public function tearDown(): void
	{
		\Configs::set('renamer_enabled', false);
		$this->resetSe();
		parent::tearDown();
	}

	private function makeTitleSortedChild(string $title): Album
	{
		$parent = Album::factory()->as_root()->owned_by($this->userMayUpload1)->create();
		$parent->album_sorting = new AlbumSortingCriterion(ColumnSortingType::TITLE, OrderSortingType::ASC);
		$parent->save();

		return Album::factory()->children_of($parent)->owned_by($this->userMayUpload1)->with_title($title)->create();
	}

	public function testBulkRenamerRecomputesAlbumBucketKey(): void
	{
		$child = $this->makeTitleSortedChild('OLD Trip');

		$response = $this->actingAs($this->userMayUpload1)->patchJson('Renamer', [
			'album_ids' => [$child->id],
		]);
		$this->assertNoContent($response);

		$child->refresh();
		$this->assertSame('2024-03 Trip', $child->title);
		$this->assertSame('2024-03-01', $child->bucket_key);
	}

	public function testBulkRenamerDispatchesAlbumSavedForRenamedAlbumsOnly(): void
	{
		$renamed = $this->makeTitleSortedChild('OLD Trip');
		$untouched = $this->makeTitleSortedChild('Holiday');

		Event::fake([AlbumSaved::class]);
		$response = $this->actingAs($this->userMayUpload1)->patchJson('Renamer', [
			'album_ids' => [$renamed->id, $untouched->id],
		]);
		$this->assertNoContent($response);

		Event::assertDispatched(AlbumSaved::class, fn (AlbumSaved $event) => $event->album_ids === [$renamed->id] &&
			$event->parent_ids === [$renamed->parent_id]);
		Event::assertDispatchedTimes(AlbumSaved::class, 1);
	}
}
