<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace Tests\Precomputing\CoverSelection;

use App\Events\PhotoListingCacheFlushRequested;
use App\Jobs\RecomputeAllPhotoBucketsJob;
use App\Models\Album;
use App\Models\Photo;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Tests\Precomputing\Base\BasePrecomputingTest;

/**
 * Test RecomputeAllPhotoBucketsJob (FR-092-13, FR-092-14, S-092-09,
 * S-092-12): every `photo_album` key is rewritten in chunks of 1,000, then
 * every cached photo listing is flushed.
 */
class RecomputeAllPhotoBucketsJobTest extends BasePrecomputingTest
{
	public function testRewritesEveryLinkInChunksAndFlushesListings(): void
	{
		DB::table('configs')->where('key', '=', 'sorting_photos_col')->update(['value' => 'is_highlighted']);
		$user = User::factory()->create();
		$album_a = Album::factory()->as_root()->owned_by($user)->create();
		$album_b = Album::factory()->as_root()->owned_by($user)->create();
		$template = (array) DB::table('photos')->where('id', '=', Photo::factory()->owned_by($user)->in($album_a)->create()->id)->first();
		$rows = [];
		$links = [];
		for ($i = 0; $i < 1000; $i++) {
			$id = sprintf('every%019d', $i);
			$rows[] = array_merge($template, ['id' => $id, 'is_highlighted' => $i % 2 === 0]);
			$links[] = ['photo_id' => $id, 'album_id' => $i < 500 ? $album_a->id : $album_b->id, 'bucket_key' => 'stale'];
		}
		foreach (array_chunk($rows, 100) as $chunk) {
			DB::table('photos')->insert($chunk);
		}
		foreach (array_chunk($links, 500) as $chunk) {
			DB::table('photo_album')->insert($chunk);
		}

		Event::fake([PhotoListingCacheFlushRequested::class]);
		DB::flushQueryLog();
		DB::enableQueryLog();
		(new RecomputeAllPhotoBucketsJob())->handle();
		$log = DB::getQueryLog();
		DB::flushQueryLog();
		DB::disableQueryLog();

		$write_queries = array_filter($log, fn (array $q) => preg_match('/^(update|insert)/i', trim($q['query'])) === 1);
		$this->assertCount(2, $write_queries);
		$this->assertSame(0, DB::table('photo_album')->where('bucket_key', '=', 'stale')->count());
		$this->assertSame('1', DB::table('photo_album')->where('photo_id', '=', sprintf('every%019d', 0))->value('bucket_key'));
		$this->assertSame('0', DB::table('photo_album')->where('photo_id', '=', sprintf('every%019d', 999))->value('bucket_key'));
		Event::assertDispatched(PhotoListingCacheFlushRequested::class);
	}
}
