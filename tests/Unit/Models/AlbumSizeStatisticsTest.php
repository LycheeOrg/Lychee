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

namespace Tests\Unit\Models;

use App\Models\Album;
use App\Models\AlbumSizeStatistics;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\AbstractTestCase;

class AlbumSizeStatisticsTest extends AbstractTestCase
{
	use RefreshDatabase;

	public function testModelCreation(): void
	{
		$user = User::factory()->may_administrate()->create();
		$album = Album::factory()->as_root()->owned_by($user)->create();

		$stats = AlbumSizeStatistics::create([
			'album_id' => $album->id,
			'size_thumb' => 1024,
			'size_thumb2x' => 2048,
			'size_small' => 4096,
			'size_small2x' => 8192,
			'size_medium' => 16384,
			'size_medium2x' => 32768,
			'size_original' => 65536,
		]);

		self::assertInstanceOf(AlbumSizeStatistics::class, $stats);
		self::assertEquals($album->id, $stats->album_id);
		self::assertEquals(1024, $stats->size_thumb);
		self::assertEquals(2048, $stats->size_thumb2x);
		self::assertEquals(4096, $stats->size_small);
		self::assertEquals(8192, $stats->size_small2x);
		self::assertEquals(16384, $stats->size_medium);
		self::assertEquals(32768, $stats->size_medium2x);
		self::assertEquals(65536, $stats->size_original);
	}

	public function testBelongsToAlbumRelationship(): void
	{
		$user = User::factory()->may_administrate()->create();
		$album = Album::factory()->as_root()->owned_by($user)->create();

		$stats = AlbumSizeStatistics::create([
			'album_id' => $album->id,
			'size_thumb' => 1024,
			'size_thumb2x' => 2048,
			'size_small' => 4096,
			'size_small2x' => 8192,
			'size_medium' => 16384,
			'size_medium2x' => 32768,
			'size_original' => 65536,
		]);

		self::assertInstanceOf(Album::class, $stats->album);
		self::assertEquals($album->id, $stats->album->id);
	}

	public function testAlbumHasOneStatisticsRelationship(): void
	{
		$user = User::factory()->may_administrate()->create();
		$album = Album::factory()->as_root()->owned_by($user)->create();

		$stats = AlbumSizeStatistics::create([
			'album_id' => $album->id,
			'size_thumb' => 1024,
			'size_thumb2x' => 2048,
			'size_small' => 4096,
			'size_small2x' => 8192,
			'size_medium' => 16384,
			'size_medium2x' => 32768,
			'size_original' => 65536,
		]);

		// Refresh album to load relationship
		$album->refresh();

		self::assertInstanceOf(AlbumSizeStatistics::class, $album->sizeStatistics);
		self::assertEquals($stats->album_id, $album->sizeStatistics->album_id);
	}

	public function testFillableFields(): void
	{
		$user = User::factory()->may_administrate()->create();
		$album = Album::factory()->as_root()->owned_by($user)->create();

		$stats = new AlbumSizeStatistics();
		$stats->fill([
			'album_id' => $album->id,
			'size_thumb' => 100,
			'size_thumb2x' => 200,
			'size_small' => 300,
			'size_small2x' => 400,
			'size_medium' => 500,
			'size_medium2x' => 600,
			'size_original' => 700,
		]);
		$stats->save();

		self::assertEquals($album->id, $stats->album_id);
		self::assertEquals(100, $stats->size_thumb);
		self::assertEquals(200, $stats->size_thumb2x);
		self::assertEquals(300, $stats->size_small);
		self::assertEquals(400, $stats->size_small2x);
		self::assertEquals(500, $stats->size_medium);
		self::assertEquals(600, $stats->size_medium2x);
		self::assertEquals(700, $stats->size_original);
	}

	public function testCasts(): void
	{
		$user = User::factory()->may_administrate()->create();
		$album = Album::factory()->as_root()->owned_by($user)->create();

		$stats = AlbumSizeStatistics::create([
			'album_id' => $album->id,
			'size_thumb' => '1024',
			'size_thumb2x' => '2048',
			'size_small' => '4096',
			'size_small2x' => '8192',
			'size_medium' => '16384',
			'size_medium2x' => '32768',
			'size_original' => '65536',
		]);

		// Verify all size fields are cast to integers
		self::assertIsInt($stats->size_thumb);
		self::assertIsInt($stats->size_thumb2x);
		self::assertIsInt($stats->size_small);
		self::assertIsInt($stats->size_small2x);
		self::assertIsInt($stats->size_medium);
		self::assertIsInt($stats->size_medium2x);
		self::assertIsInt($stats->size_original);
	}

	public function testNoTimestamps(): void
	{
		$user = User::factory()->may_administrate()->create();
		$album = Album::factory()->as_root()->owned_by($user)->create();

		$stats = AlbumSizeStatistics::create([
			'album_id' => $album->id,
			'size_thumb' => 1024,
			'size_thumb2x' => 2048,
			'size_small' => 4096,
			'size_small2x' => 8192,
			'size_medium' => 16384,
			'size_medium2x' => 32768,
			'size_original' => 65536,
		]);

		// Verify that the model has timestamps disabled
		self::assertFalse($stats->timestamps);
		// Verify that created_at and updated_at are not set
		self::assertNull($stats->created_at);
		self::assertNull($stats->updated_at);
	}

	public function testUpdateOrCreate(): void
	{
		$user = User::factory()->may_administrate()->create();
		$album = Album::factory()->as_root()->owned_by($user)->create();

		// Create initial statistics
		$stats1 = AlbumSizeStatistics::updateOrCreate(
			['album_id' => $album->id],
			[
				'size_thumb' => 1024,
				'size_thumb2x' => 2048,
				'size_small' => 4096,
				'size_small2x' => 8192,
				'size_medium' => 16384,
				'size_medium2x' => 32768,
				'size_original' => 65536,
			]
		);

		self::assertEquals(1024, $stats1->size_thumb);

		// Update existing statistics
		$stats2 = AlbumSizeStatistics::updateOrCreate(
			['album_id' => $album->id],
			[
				'size_thumb' => 2048,
				'size_thumb2x' => 4096,
				'size_small' => 8192,
				'size_small2x' => 16384,
				'size_medium' => 32768,
				'size_medium2x' => 65536,
				'size_original' => 131072,
			]
		);

		self::assertEquals($stats1->album_id, $stats2->album_id);
		self::assertEquals(2048, $stats2->size_thumb);

		// Verify only one record exists
		self::assertEquals(1, AlbumSizeStatistics::where('album_id', $album->id)->count());
	}

	public function testCascadeDeleteOnAlbumDeletion(): void
	{
		$user = User::factory()->may_administrate()->create();
		$album = Album::factory()->as_root()->owned_by($user)->create();

		$stats = AlbumSizeStatistics::create([
			'album_id' => $album->id,
			'size_thumb' => 1024,
			'size_thumb2x' => 2048,
			'size_small' => 4096,
			'size_small2x' => 8192,
			'size_medium' => 16384,
			'size_medium2x' => 32768,
			'size_original' => 65536,
		]);

		// Verify statistics exist
		self::assertNotNull(AlbumSizeStatistics::find($album->id));

		// Delete album
		$album->delete();

		// Verify statistics were cascade deleted
		self::assertNull(AlbumSizeStatistics::find($album->id));
	}
}
