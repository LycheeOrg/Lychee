<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Actions\Tag;

use Illuminate\Contracts\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

trait TagCleanupTrait
{
	/**
	 * Cleans up tags that are not linked to any photos.
	 */
	protected function cleanupUnusedTags(): void
	{
		DB::table('tags')
			->whereNotExists(function (Builder $query): void {
				$query->select(DB::raw(1))
					->from('photos_tags')
					->whereColumn('photos_tags.tag_id', 'tags.id');
			})
			->delete();
	}
}