<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Output\ConsoleOutput;

require_once 'TemporaryModels/BucketKeyBackfill.php';
require_once 'TemporaryModels/BucketKeyAlbum.php';

/**
 * Feature 091 (FR-091-16): rewrites every `albums.bucket_key` and
 * `photo_album.bucket_key` in the granularity-free format (FR-091-03,
 * FR-091-04), chunked. Runs after `photos.taken_key`/`title_date_key` are
 * backfilled. Idempotent.
 */
return new class() extends Migration {
	/**
	 * Run the migrations.
	 */
	public function up(): void
	{
		$this->backfillAlbums();
		$this->backfillPhotoAlbum();
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
		// Sic! Intentionally a no-op: after a rollback, the earlier version's
		// `lychee:recompute-buckets` rewrites the ids it expects.
	}

	private function backfillAlbums(): void
	{
		$default_column = BucketKeyBackfill::config('sorting_albums_col', 'created_at');
		$title_mode = BucketKeyBackfill::config('title_bucket_mode', 'date_prefix');

		$query = DB::table('albums as a')
			->join('base_albums as ab', 'ab.id', '=', 'a.id')
			->leftJoin('albums as p', 'p.id', '=', 'a.parent_id')
			->select(['a.id', 'ab.title', 'ab.title_base', 'ab.created_at', 'a.min_taken_at', 'a.max_taken_at', 'p.album_sorting_col', 'p.album_sorting_order']);

		$progress_bar = $this->progressBar("Backfilling 'albums' bucket keys", DB::table('albums')->count());

		$query->orderBy('a.id')->chunkById(BucketKeyBackfill::CHUNK_SIZE, function (Collection $albums) use ($default_column, $title_mode, $progress_bar) {
			$values = $albums->map(fn ($album) => [
				'id' => $album->id,
				'bucket_key' => BucketKeyBackfill::albumKey(
					BucketKeyBackfill::effectiveColumn($album->album_sorting_col, $album->album_sorting_order, $default_column),
					$title_mode,
					$album,
				),
			])->all();

			// https://github.com/mavinoo/laravelBatch
			batch()->update(new BucketKeyAlbum(), $values, 'id');

			$progress_bar?->advance($albums->count());
		}, column: 'a.id', alias: 'id');

		$progress_bar?->finish();
	}

	private function backfillPhotoAlbum(): void
	{
		$default_column = BucketKeyBackfill::config('sorting_photos_col', 'created_at');
		$title_mode = BucketKeyBackfill::config('photo_title_bucket_mode', 'date_prefix');

		$progress_bar = $this->progressBar("Backfilling 'photo_album' bucket keys", DB::table('photo_album')->count());

		$cursor = null;
		do {
			$keys = $this->keyPage($cursor);
			$updates = $this->photoAlbumKeys($keys, $default_column, $title_mode);
			if ($updates !== []) {
				DB::table('photo_album')->upsert($updates, ['photo_id', 'album_id'], ['bucket_key']);
			}

			$progress_bar?->advance($keys->count());
			$last = $keys->last();
			$cursor = $last !== null ? [$last->photo_id, $last->album_id] : null;
		} while ($keys->count() === BucketKeyBackfill::CHUNK_SIZE);

		$progress_bar?->finish();
	}

	/**
	 * Keyset page of `photo_album` primary keys after `$cursor`.
	 *
	 * @param array{0:string,1:string}|null $cursor
	 *
	 * @return Collection<int,object{photo_id:string,album_id:string}>
	 */
	private function keyPage(?array $cursor): Collection
	{
		$query = DB::table('photo_album')
			->select(['photo_id', 'album_id'])
			->orderBy('photo_id')
			->orderBy('album_id')
			->limit(BucketKeyBackfill::CHUNK_SIZE);

		if ($cursor !== null) {
			[$photo_id, $album_id] = $cursor;
			$query->where('photo_id', '>=', $photo_id)
				->where(fn ($q) => $q->where('photo_id', '>', $photo_id)->orWhere('album_id', '>', $album_id));
		}

		return $query->get();
	}

	/**
	 * @param Collection<int,object{photo_id:string,album_id:string}> $keys
	 *
	 * @return array<int,array{photo_id:string,album_id:string,bucket_key:string|null}>
	 */
	private function photoAlbumKeys(Collection $keys, string $default_column, string $title_mode): array
	{
		$photos = DB::table('photos')
			->select(['id', 'title_base', 'title_date_key', 'created_at', 'taken_key', 'is_highlighted', 'type', 'rating_avg'])
			->whereIn('id', $keys->pluck('photo_id')->unique()->all())
			->get()
			->keyBy('id');

		$albums = DB::table('base_albums')
			->select(['id', 'sorting_col', 'sorting_order'])
			->whereIn('id', $keys->pluck('album_id')->unique()->all())
			->get()
			->keyBy('id');

		$updates = [];
		foreach ($keys as $key) {
			$photo = $photos->get($key->photo_id);
			$album = $albums->get($key->album_id);
			if ($photo === null || $album === null) {
				continue;
			}

			$updates[] = [
				'photo_id' => $key->photo_id,
				'album_id' => $key->album_id,
				'bucket_key' => BucketKeyBackfill::photoKey(
					BucketKeyBackfill::effectiveColumn($album->sorting_col, $album->sorting_order, $default_column),
					$title_mode,
					$photo,
				),
			];
		}

		return $updates;
	}

	private function progressBar(string $label, int $total): ?ProgressBar
	{
		if (App::runningUnitTests()) {
			return null;
		}

		$progress_bar = new ProgressBar(new ConsoleOutput());
		$progress_bar->setFormat($label . ' %current%/%max% [%bar%] %percent:3s%%');
		$progress_bar->start($total);

		return $progress_bar;
	}
};
