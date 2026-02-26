<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Actions\Renamer;

use App\Http\Resources\Models\RenamerPreviewResource;
use App\Metadata\Renamer\AlbumRenamer;
use App\Models\Album;
use Illuminate\Support\Collection;

class PreviewAlbums
{
	private const CHUNK_SIZE = 100;

	/**
	 * Preview renamer rule application on albums.
	 *
	 * @param int           $user_id   The user ID
	 * @param array<int>    $rule_ids  The rule IDs to apply
	 * @param array<string> $album_ids Explicit album IDs (if provided)
	 * @param string        $parent_id Parent album ID for scope-based preview
	 * @param string        $scope     'current' or 'descendants'
	 *
	 * @return Collection<int,RenamerPreviewResource>
	 */
	public function execute(
		int $user_id,
		array $rule_ids,
		array $album_ids = [],
		string $parent_id = '',
		string $scope = 'current',
	): Collection {
		$renamer = new AlbumRenamer($user_id, $rule_ids);
		$changes = [];

		// Resolve albums: explicit IDs or by parent_id + scope
		$query = Album::query();
		if (count($album_ids) > 0) {
			$query->whereIn('id', $album_ids);
		} elseif ($parent_id !== '') {
			$parent = Album::query()->findOrFail($parent_id);
			if ($scope === 'descendants') {
				$query->where('_lft', '>', $parent->_lft)
					->where('_rgt', '<', $parent->_rgt);
			} else {
				$query->where('parent_id', $parent_id);
			}
		}

		$query->chunkById(self::CHUNK_SIZE, function (Collection $albums) use ($renamer, &$changes): void {
			foreach ($albums as $album) {
				$new_title = $renamer->handle($album->title);
				if ($new_title !== $album->title) {
					$changes[] = new RenamerPreviewResource($album->id, $album->title, $new_title);
				}
			}
		});

		return collect($changes);
	}
}
