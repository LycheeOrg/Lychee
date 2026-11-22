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

namespace Tests\Traits;

use App\Enum\SizeVariantType;
use App\Models\Album;
use App\Models\Photo;
use App\Models\TagAlbum;
use App\Models\User;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Support\Facades\DB;

/**
 * Bulk fixtures for the Feature 112 bind-limit tests: thousands of photos,
 * albums, tag albums and groups inserted with a few multi-row inserts,
 * cloned from one factory-made row each.
 */
trait CreatesBulkFixtures
{
	/** Rows per multi-row insert, so that a fixture insert stays small. */
	private int $bulk_insert_rows = 50;

	/** Largest number of bindings of a query seen since {@see self::listenToBindings()}. */
	protected int $max_bindings = 0;

	/** SQL of that query. */
	protected string $max_bindings_sql = '';

	protected static function bulkRandomId(): string
	{
		return strtr(base64_encode(random_bytes(18)), '+/', '-_');
	}

	/**
	 * Record the largest number of bindings of any query from now on.
	 */
	protected function listenToBindings(): void
	{
		$this->max_bindings = 0;
		DB::listen(function (QueryExecuted $query): void {
			if (count($query->bindings) > $this->max_bindings) {
				$this->max_bindings = count($query->bindings);
				$this->max_bindings_sql = substr($query->sql, 0, 300);
			}
		});
	}

	protected function assertBindingsWithinLimit(): void
	{
		self::assertLessThanOrEqual(1000, $this->max_bindings, 'Query with ' . $this->max_bindings . ' bindings: ' . $this->max_bindings_sql);
	}

	/**
	 * Insert $count photos owned by $owner and linked to every album of $album_ids.
	 * The first one is a factory photo with all size variants; the others are
	 * clones with one ORIGINAL size variant each.
	 *
	 * @param string[] $album_ids
	 *
	 * @return string[] photo ids
	 */
	protected function bulkPhotos(User $owner, int $count, array $album_ids): array
	{
		$template = Photo::factory()->owned_by($owner)->create();
		$photo_row = (array) DB::table('photos')->where('id', '=', $template->id)->first();
		$variant_row = (array) DB::table('size_variants')->where('photo_id', '=', $template->id)->where('type', '=', SizeVariantType::ORIGINAL->value)->first();
		unset($variant_row['id']);

		$ids = [$template->id];
		$photos = [];
		$variants = [];
		for ($i = 1; $i < $count; $i++) {
			$id = self::bulkRandomId();
			$ids[] = $id;
			$photos[] = ['id' => $id, 'title' => 'bulk ' . $i] + $photo_row;
			$variants[] = ['photo_id' => $id, 'short_path' => 'bulk/' . $id . '.jpg'] + $variant_row;
		}
		$this->bulkInsert('photos', $photos);
		$this->bulkInsert('size_variants', $variants);

		$links = [];
		foreach ($album_ids as $album_id) {
			foreach ($ids as $photo_id) {
				$links[] = ['album_id' => $album_id, 'photo_id' => $photo_id];
			}
		}
		$this->bulkInsert('photo_album', $links);

		return $ids;
	}

	/**
	 * Insert one photo owned by $owner in each album of $album_ids: a factory
	 * photo in the first one, clones with one ORIGINAL size variant in the others.
	 *
	 * @param string[] $album_ids
	 *
	 * @return string[] photo ids, in the order of $album_ids
	 */
	protected function bulkPhotosIn(User $owner, array $album_ids): array
	{
		$template = Photo::factory()->owned_by($owner)->create();
		$photo_row = (array) DB::table('photos')->where('id', '=', $template->id)->first();
		$variant_row = (array) DB::table('size_variants')->where('photo_id', '=', $template->id)->where('type', '=', SizeVariantType::ORIGINAL->value)->first();
		unset($variant_row['id']);

		// The factory photo goes into the first album, clones into the others.
		$ids = [];
		$photos = [];
		$variants = [];
		$links = [];
		foreach (array_values($album_ids) as $i => $album_id) {
			if ($i === 0) {
				$ids[] = $template->id;
				$links[] = ['album_id' => $album_id, 'photo_id' => $template->id];
				continue;
			}
			$id = self::bulkRandomId();
			$ids[] = $id;
			$photos[] = ['id' => $id, 'title' => 'bulk in ' . $i] + $photo_row;
			$variants[] = ['photo_id' => $id, 'short_path' => 'bulk/' . $id . '.jpg'] + $variant_row;
			$links[] = ['album_id' => $album_id, 'photo_id' => $id];
		}
		$this->bulkInsert('photos', $photos);
		$this->bulkInsert('size_variants', $variants);
		$this->bulkInsert('photo_album', $links);

		return $ids;
	}

	/**
	 * Grant delete on every album of $album_ids, to a user or to a group.
	 *
	 * @param string[] $album_ids
	 */
	protected function bulkGrantDelete(array $album_ids, ?int $user_id, ?int $user_group_id = null): void
	{
		$this->bulkInsert('access_permissions', array_map(fn (string $album_id) => [
			'base_album_id' => $album_id,
			'user_id' => $user_id,
			'user_group_id' => $user_group_id,
			'grants_delete' => true,
			'created_at' => now(),
			'updated_at' => now(),
		], $album_ids));
	}

	/**
	 * Insert $count leaf albums under $parent, keeping a valid nested set.
	 *
	 * @return string[] album ids
	 */
	protected function bulkChildAlbums(Album $parent, User $owner, int $count): array
	{
		$base_row = (array) DB::table('base_albums')->where('id', '=', $parent->id)->first();
		$album_row = (array) DB::table('albums')->where('id', '=', $parent->id)->first();
		$rgt = intval($album_row['_rgt']);

		// Open a gap of 2 * $count right before the parent's right bound.
		DB::table('albums')->where('_rgt', '>=', $rgt)->update(['_rgt' => DB::raw('_rgt + ' . (2 * $count))]);
		DB::table('albums')->where('_lft', '>', $rgt)->update(['_lft' => DB::raw('_lft + ' . (2 * $count))]);

		$ids = [];
		$bases = [];
		$albums = [];
		for ($i = 0; $i < $count; $i++) {
			$id = self::bulkRandomId();
			$ids[] = $id;
			$bases[] = ['id' => $id, 'title' => 'bulk ' . $i, 'title_base' => 'bulk ' . $i, 'slug' => null, 'owner_id' => $owner->id] + $base_row;
			$albums[] = [
				'id' => $id,
				'parent_id' => $parent->id,
				'_lft' => $rgt + 2 * $i,
				'_rgt' => $rgt + 2 * $i + 1,
				'cover_id' => null,
				'header_id' => null,
				'auto_cover_id_max_privilege' => null,
				'auto_cover_id_least_privilege' => null,
				'num_children' => 0,
				'num_photos' => 0,
			] + $album_row;
		}
		$this->bulkInsert('base_albums', $bases);
		$this->bulkInsert('albums', $albums);

		return $ids;
	}

	/**
	 * Insert $count tag albums owned by $owner, cloned from $template.
	 *
	 * @return string[] tag album ids
	 */
	protected function bulkTagAlbums(TagAlbum $template, User $owner, int $count): array
	{
		$base_row = (array) DB::table('base_albums')->where('id', '=', $template->id)->first();
		$tag_row = (array) DB::table('tag_albums')->where('id', '=', $template->id)->first();

		$ids = [];
		$bases = [];
		$tag_albums = [];
		for ($i = 0; $i < $count; $i++) {
			$id = self::bulkRandomId();
			$ids[] = $id;
			$bases[] = ['id' => $id, 'title' => 'bulk tag ' . $i, 'title_base' => 'bulk tag ' . $i, 'slug' => null, 'owner_id' => $owner->id] + $base_row;
			$tag_albums[] = ['id' => $id, 'cover_id' => null] + $tag_row;
		}
		$this->bulkInsert('base_albums', $bases);
		$this->bulkInsert('tag_albums', $tag_albums);

		return $ids;
	}

	/**
	 * Insert $count user groups with $user as member.
	 *
	 * @return int[] group ids
	 */
	protected function bulkGroupsFor(User $user, int $count): array
	{
		$prefix = 'bulk-' . self::bulkRandomId() . '-';
		$groups = [];
		for ($i = 0; $i < $count; $i++) {
			$groups[] = ['name' => $prefix . $i, 'description' => null, 'updated_at' => now()];
		}
		$this->bulkInsert('user_groups', $groups);
		$ids = DB::table('user_groups')->where('name', 'like', $prefix . '%')->orderBy('id')->pluck('id')->all();

		$this->bulkInsert('users_user_groups', array_map(fn (int $id) => ['user_id' => $user->id, 'user_group_id' => $id, 'role' => 'member'], $ids));

		return $ids;
	}

	/**
	 * @param array<int,array<string,mixed>> $rows
	 */
	private function bulkInsert(string $table, array $rows): void
	{
		foreach (array_chunk($rows, $this->bulk_insert_rows) as $chunk) {
			DB::table($table)->insert($chunk);
		}
	}
}
