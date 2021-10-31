<?php

/**
 * This file is copied & pasted from `vendor/kalnoy/nestedset/src/NestedSet.php`.
 * This file patches the method {@link \Kalnoy\Nestedset\NestedSet::isNode()},
 * see [NestedSet issue #538](https://github.com/lazychaser/laravel-nestedset/issues/538).
 *
 * Note, the command `composer install -o` prints a warning that the class
 * {@link \Kalnoy\Nestedset\NestedSet} is defined twice (here and in
 * `vendor/kalnoy/nestedset/src/NestedSet.php`).
 * It is important to keep the order of folders for auto-loading in
 * `composer.json` intact such that this patched version takes precedence.
 */

namespace Kalnoy\Nestedset;

use Illuminate\Database\Schema\Blueprint;

class NestedSet
{
	/**
	 * The name of default lft column.
	 */
	const LFT = '_lft';

	/**
	 * The name of default rgt column.
	 */
	const RGT = '_rgt';

	/**
	 * The name of default parent id column.
	 */
	const PARENT_ID = 'parent_id';

	/**
	 * Insert direction.
	 */
	const BEFORE = 1;

	/**
	 * Insert direction.
	 */
	const AFTER = 2;

	/**
	 * Add default nested set columns to the table. Also create an index.
	 *
	 * @param \Illuminate\Database\Schema\Blueprint $table
	 */
	public static function columns(Blueprint $table)
	{
		$table->unsignedInteger(self::LFT)->default(0);
		$table->unsignedInteger(self::RGT)->default(0);
		$table->unsignedInteger(self::PARENT_ID)->nullable();

		$table->index(static::getDefaultColumns());
	}

	/**
	 * Drop NestedSet columns.
	 *
	 * @param \Illuminate\Database\Schema\Blueprint $table
	 */
	public static function dropColumns(Blueprint $table)
	{
		$columns = static::getDefaultColumns();

		$table->dropIndex($columns);
		$table->dropColumn($columns);
	}

	/**
	 * Get a list of default columns.
	 *
	 * @return array
	 */
	public static function getDefaultColumns()
	{
		return [static::LFT, static::RGT, static::PARENT_ID];
	}

	/**
	 * Replaces instanceof calls for this trait.
	 *
	 * @param mixed $node
	 *
	 * @return bool
	 */
	public static function isNode($node)
	{
		// This is the patched line.
		// The whole file (actually the whole directory) will become obsolete,
		// when
		// [NestedSet issue #538](https://github.com/lazychaser/laravel-nestedset/issues/538)
		// has been fixed.
		// TODO: Track upstream library if this gets fixed.
		return is_object($node) && $node instanceof Node;
	}
}
