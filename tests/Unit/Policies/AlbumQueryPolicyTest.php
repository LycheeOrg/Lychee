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

namespace Tests\Unit\Policies;

use App\Models\Album;
use App\Models\User;
use App\Policies\AlbumQueryPolicy;
use Illuminate\Support\Facades\DB;
use Tests\AbstractTestCase;
use Tests\Traits\RequiresEmptyAlbums;
use Tests\Traits\RequiresEmptyUsers;

/**
 * Covers Feature 057 T-057-01: {@see AlbumQueryPolicy::joinBaseAlbumBulkEditFields()}.
 */
class AlbumQueryPolicyTest extends AbstractTestCase
{
	use RequiresEmptyUsers;
	use RequiresEmptyAlbums;

	private AlbumQueryPolicy $policy;

	protected function setUp(): void
	{
		parent::setUp();
		$this->setUpRequiresEmptyUsers();
		$this->setUpRequiresEmptyAlbums();
		$this->policy = new AlbumQueryPolicy();
	}

	protected function tearDown(): void
	{
		$this->tearDownRequiresEmptyAlbums();
		$this->tearDownRequiresEmptyUsers();
		parent::tearDown();
	}

	public function testJoinBaseAlbumBulkEditFieldsProvidesExpectedColumns(): void
	{
		$user = User::factory()->create();
		$album = Album::factory()->as_root()->owned_by($user)->create();

		DB::table('base_albums')->where('id', '=', $album->id)->update([
			'copyright' => 'Copyright text',
			'photo_layout' => 'square',
			'sorting_col' => 'created_at',
			'sorting_order' => 'DESC',
			'photo_timeline' => 'day',
		]);

		$query = DB::table('albums')->where('albums.id', '=', $album->id)->select(['albums.id']);
		$this->policy->joinBaseAlbumBulkEditFields($query, 'albums.id', 'bulk_');
		$query->addSelect([
			'bulk_base_albums.copyright',
			'bulk_base_albums.photo_layout',
			'bulk_base_albums.sorting_col as photo_sorting_col',
			'bulk_base_albums.sorting_order as photo_sorting_order',
			'bulk_base_albums.photo_timeline',
		]);

		$row = $query->first();

		self::assertNotNull($row);
		self::assertSame('Copyright text', $row->copyright);
		self::assertSame('square', $row->photo_layout);
		self::assertSame('created_at', $row->photo_sorting_col);
		self::assertSame('DESC', $row->photo_sorting_order);
		self::assertSame('day', $row->photo_timeline);
	}

	/**
	 * The helper must join under its own alias (a non-empty prefix), so it
	 * can coexist with {@see AlbumQueryPolicy::joinBaseAlbumOwnerId()}'s
	 * default `base_albums` alias already present on a policy-prepared query
	 * without a SQL alias collision.
	 */
	public function testJoinBaseAlbumBulkEditFieldsDoesNotCollideWithDefaultBaseAlbumsJoin(): void
	{
		$user = User::factory()->create();
		$album = Album::factory()->as_root()->owned_by($user)->create();

		DB::table('base_albums')->where('id', '=', $album->id)->update([
			'copyright' => 'Another copyright',
		]);

		$query = Album::query()->select(['albums.id'])->where('albums.id', '=', $album->id);
		$this->policy->applyVisibilityFilter($query, $user);
		$this->policy->joinBaseAlbumBulkEditFields($query, 'albums.id', 'bulk_');
		$query->addSelect(['bulk_base_albums.copyright']);

		$row = $query->toBase()->first();

		self::assertNotNull($row);
		self::assertSame('Another copyright', $row->copyright);
	}
}
