<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Models\Scopes;

use App\Models\Photo;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Database\Query\Builder as BaseBuilder;
use Illuminate\Support\Facades\DB;

/**
 * Feature 119 (FR-119-03, DO-119-02): hides the photos waiting in the trash.
 *
 * Registered on {@see \App\Models\Photo}, so every model query skips them,
 * for owners and admins too. Raw `photos` reads that list or serve photos
 * call {@see self::exclude()} with their photo id column. The trash action
 * and the purge read without the scope.
 *
 * @implements Scope<Photo>
 */
class NotTrashedScope implements Scope
{
	public const TABLE = 'trashed_photos';

	/**
	 * @param Builder<covariant Photo> $builder
	 * @param Photo                    $model
	 */
	public function apply(Builder $builder, Model $model): void
	{
		self::exclude($builder->getQuery(), $model->qualifyColumn($model->getKeyName()));
	}

	/**
	 * Adds `NOT EXISTS (a trashed_photos row for $photo_id_column)`.
	 *
	 * @param string $photo_id_column qualified column holding the photo id
	 */
	public static function exclude(BaseBuilder $query, string $photo_id_column): BaseBuilder
	{
		return $query->whereNotExists(fn (BaseBuilder $trashed) => $trashed
			->select(DB::raw(1))
			->from(self::TABLE)
			->whereColumn(self::TABLE . '.photo_id', '=', $photo_id_column));
	}
}
