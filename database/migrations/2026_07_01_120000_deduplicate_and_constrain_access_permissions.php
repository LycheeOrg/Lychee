<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Query\Builder;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration {
	private const TABLE_NAME = 'access_permissions';

	private const BASE_ALBUM_ID = 'base_album_id';
	private const USER_ID = 'user_id';
	private const USER_GROUP_ID = 'user_group_id';

	// Generated columns coalescing the nullable USER_ID / USER_GROUP_ID so that
	// a plain unique index can actually enforce uniqueness for both of them.
	// (A unique index ignores NULL, so [base_album_id, user_id, user_group_id]
	// alone would never catch duplicate group permissions, since user_id is
	// always NULL for those rows.)
	private const USER_ID_UNIQUE_KEY = 'user_id_unique_key';
	private const USER_GROUP_ID_UNIQUE_KEY = 'user_group_id_unique_key';

	// Explicit name because Laravel's auto-generated name (concatenating all
	// four column names) exceeds MySQL's 64-character identifier limit.
	private const UNIQUE_INDEX_NAME = 'access_permissions_dedup_unique';

	/**
	 * Run the migrations.
	 */
	public function up(): void
	{
		if (!App::runningUnitTests()) {
			// @codeCoverageIgnoreStart
			DB::transaction(fn () => $this->deduplicate());
		// @codeCoverageIgnoreEnd
		} else {
			$this->deduplicate();
		}

		Schema::table(self::TABLE_NAME, function (Blueprint $table) {
			// MySQL/MariaDB refuses to add a STORED generated column to a table
			// that still has an active foreign key: doing so requires an
			// in-place table rebuild, which InnoDB rejects with error 1215
			// ("Cannot add foreign key constraint"). Drop it here and
			// recreate it once the new columns are in place.
			$table->dropForeign([self::USER_ID]);
			$table->dropUnique([self::BASE_ALBUM_ID, self::USER_ID]);
			$table->unsignedInteger(self::USER_ID_UNIQUE_KEY)->nullable(false)->storedAs('COALESCE(' . self::USER_ID . ', 0)');
			$table->unsignedInteger(self::USER_GROUP_ID_UNIQUE_KEY)->nullable(false)->storedAs('COALESCE(' . self::USER_GROUP_ID . ', 0)');
		});

		Schema::table(self::TABLE_NAME, function (Blueprint $table) {
			$table->unique([self::BASE_ALBUM_ID, self::USER_ID_UNIQUE_KEY, self::USER_GROUP_ID_UNIQUE_KEY], self::UNIQUE_INDEX_NAME);
			// MySQL prohibits CASCADE/SET NULL referential actions on a column
			// that is a base column of a generated column (user_id_unique_key
			// depends on user_id here), so this can no longer cascade at the
			// DB level. This is not a behavior change in practice: User::delete()
			// already deletes the user's AccessPermission rows explicitly before
			// removing the user.
			$table->foreign(self::USER_ID)->references('id')->on('users')->restrictOnUpdate()->restrictOnDelete();
		});
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
		Schema::table(self::TABLE_NAME, function (Blueprint $table) {
			$table->dropForeign([self::USER_ID]);
			$table->dropUnique(self::UNIQUE_INDEX_NAME);
			$table->dropColumn([self::USER_ID_UNIQUE_KEY, self::USER_GROUP_ID_UNIQUE_KEY]);
		});

		Schema::table(self::TABLE_NAME, function (Blueprint $table) {
			$table->unique([self::BASE_ALBUM_ID, self::USER_ID]);
			// The generated columns are already gone at this point, so the
			// original CASCADE actions are legal again here.
			$table->foreign(self::USER_ID)->references('id')->on('users')->cascadeOnUpdate()->cascadeOnDelete();
		});
	}

	/**
	 * Remove duplicate (base_album_id, user_id, user_group_id) rows, e.g. those
	 * created by the Propagate action bug where an unscoped query leaked
	 * unrelated albums' group permissions and re-inserted them for every
	 * descendant on every click. One row per group is kept, the rest deleted.
	 */
	private function deduplicate(): void
	{
		$duplicate_groups = DB::table(self::TABLE_NAME)
			->select(self::BASE_ALBUM_ID, self::USER_ID, self::USER_GROUP_ID)
			->groupBy(self::BASE_ALBUM_ID, self::USER_ID, self::USER_GROUP_ID)
			->havingRaw('count(*) > 1')
			->get();

		foreach ($duplicate_groups as $group) {
			$scoped = $this->matchNullable(
				$this->matchNullable(
					$this->matchNullable(
						DB::table(self::TABLE_NAME),
						self::BASE_ALBUM_ID,
						$group->base_album_id
					),
					self::USER_ID,
					$group->user_id
				),
				self::USER_GROUP_ID,
				$group->user_group_id
			);

			// Keep the most recently written row: if duplicates ended up with
			// different grants (e.g. leaked from different source albums),
			// the newest one best reflects the last-applied intended state.
			$keep_id = $scoped->max('id');
			$scoped->where('id', '!=', $keep_id)->delete();
		}
	}

	/**
	 * Constrain a query builder on a column that may legitimately be NULL.
	 */
	private function matchNullable(Builder $query, string $column, string|int|null $value): Builder
	{
		return $value === null ? $query->whereNull($column) : $query->where($column, '=', $value);
	}
};
