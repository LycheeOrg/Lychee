<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Feature 076 (FR-076-01, FR-076-03, ADR-076-01): regular-album automatic
 * covers move from six `albums` columns into `album_user_thumbs` rows:
 * the max-privilege triple under `user_id = owner_id`, the least-privilege
 * triple under the single shared user (album with exactly one permission,
 * a user one, not the owner) or under `NULL`. Rows are tagged
 * `is_precomputed` so the cache purges of ADR-0010 leave them alone.
 */
return new class() extends Migration {
	public const RANDOM_ID_LENGTH = 24;

	private const TABLE = 'album_user_thumbs';
	private const FLAG = 'is_precomputed';
	private const CHUNK = 500;

	private const MAX = ['auto_cover_id_max_privilege', 'auto_cover_id_max_privilege_2', 'auto_cover_id_max_privilege_3'];
	private const LEAST = ['auto_cover_id_least_privilege', 'auto_cover_id_least_privilege_2', 'auto_cover_id_least_privilege_3'];

	public function up(): void
	{
		Schema::table(self::TABLE, function (Blueprint $table) {
			$table->boolean(self::FLAG)->default(false);
		});

		DB::table('albums')
			->join('base_albums', 'base_albums.id', '=', 'albums.id')
			->select(['albums.id', 'base_albums.owner_id', ...array_map(fn ($c) => 'albums.' . $c, [...self::MAX, ...self::LEAST])])
			->orderBy('albums.id')
			->chunk(self::CHUNK, function ($albums): void {
				$single_share = $this->singleShareUsers($albums->pluck('id')->all());
				$rows = [];
				foreach ($albums as $album) {
					$columns = (array) $album;
					$rows[] = $this->row($columns, self::MAX, (int) $album->owner_id);
					$least_key = $single_share[$album->id] ?? null;
					$rows[] = $least_key === (int) $album->owner_id ? null : $this->row($columns, self::LEAST, $least_key);
				}
				DB::table(self::TABLE)->insert(array_values(array_filter($rows, fn (?array $row): bool => $row !== null)));
			});

		Schema::table('albums', function (Blueprint $table) {
			foreach ([...self::MAX, ...self::LEAST] as $column) {
				$table->dropForeign([$column]);
			}
		});
		Schema::table('albums', function (Blueprint $table) {
			$table->dropColumn([...self::MAX, ...self::LEAST]);
		});
	}

	public function down(): void
	{
		Schema::table('albums', function (Blueprint $table) {
			foreach ([...self::MAX, ...self::LEAST] as $column) {
				$table->char($column, self::RANDOM_ID_LENGTH)->nullable();
			}
		});
		Schema::table('albums', function (Blueprint $table) {
			foreach ([...self::MAX, ...self::LEAST] as $column) {
				$table->foreign($column)->references('id')->on('photos')->onDelete('set null');
			}
		});

		DB::table(self::TABLE)
			->join('base_albums', 'base_albums.id', '=', self::TABLE . '.album_id')
			->where(self::TABLE . '.' . self::FLAG, '=', true)
			->select([self::TABLE . '.id', self::TABLE . '.album_id', self::TABLE . '.user_id', 'base_albums.owner_id', 'photo_id', 'photo_id_2', 'photo_id_3'])
			->orderBy(self::TABLE . '.id')
			->chunk(self::CHUNK, function ($rows): void {
				foreach ($rows as $row) {
					$columns = $row->user_id !== null && (int) $row->user_id === (int) $row->owner_id ? self::MAX : self::LEAST;
					DB::table('albums')->where('id', '=', $row->album_id)->update(array_combine($columns, [$row->photo_id, $row->photo_id_2, $row->photo_id_3]));
				}
			});

		DB::table(self::TABLE)->where(self::FLAG, '=', true)->delete();

		Schema::table(self::TABLE, function (Blueprint $table) {
			$table->dropColumn(self::FLAG);
		});
	}

	/**
	 * The user of the album's only permission, for albums with exactly one
	 * permission which is a user permission (mirrors
	 * `RecomputeAlbumStatsJob::computeLeastPrivilegeCovers()`).
	 *
	 * @param array<int,string> $album_ids
	 *
	 * @return array<string,int>
	 */
	private function singleShareUsers(array $album_ids): array
	{
		return DB::table('access_permissions')
			->select(['base_album_id', DB::raw('MIN(user_id) as user_id')])
			->whereIn('base_album_id', $album_ids)
			->groupBy('base_album_id')
			->havingRaw('COUNT(*) = 1')
			->get()
			->filter(fn ($p) => $p->user_id !== null)
			->mapWithKeys(fn ($p) => [$p->base_album_id => (int) $p->user_id])
			->all();
	}

	/**
	 * @param array<string,mixed> $album   the album row
	 * @param array<int,string>   $columns the three cover columns of one privilege level
	 *
	 * @return array<string,mixed>|null
	 */
	private function row(array $album, array $columns, ?int $user_id): ?array
	{
		if ($album[$columns[0]] === null) {
			return null;
		}

		return [
			'album_id' => $album['id'],
			'user_id' => $user_id,
			'photo_id' => $album[$columns[0]],
			'photo_id_2' => $album[$columns[1]],
			'photo_id_3' => $album[$columns[2]],
			self::FLAG => true,
		];
	}
};
