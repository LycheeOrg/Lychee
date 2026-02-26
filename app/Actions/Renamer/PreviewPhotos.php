<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Actions\Renamer;

use App\Http\Resources\Models\RenamerPreviewResource;
use App\Metadata\Renamer\PhotoRenamer;
use App\Models\Album;
use App\Models\Photo;
use Illuminate\Support\Collection;

class PreviewPhotos
{
	private const CHUNK_SIZE = 100;

	/**
	 * Preview renamer rule application on photos.
	 *
	 * @param int           $user_id   The user ID
	 * @param array<int>    $rule_ids  The rule IDs to apply
	 * @param array<string> $photo_ids Explicit photo IDs (if provided)
	 * @param string        $album_id  Album ID for scope-based preview
	 * @param string        $scope     'current' or 'descendants'
	 *
	 * @return Collection<int,RenamerPreviewResource>
	 */
	public function execute(
		int $user_id,
		array $rule_ids,
		array $photo_ids = [],
		string $album_id = '',
		string $scope = 'current',
	): Collection {
		$renamer = new PhotoRenamer($user_id, $rule_ids);
		$changes = [];

		// Resolve photos: explicit IDs or by album_id + scope
		$query = Photo::query();
		if (count($photo_ids) > 0) {
			$query->whereIn('id', $photo_ids);
		} elseif ($album_id !== '') {
			if ($scope === 'descendants') {
				$parent = Album::query()->findOrFail($album_id);
				$descendant_ids = Album::query()
					->where('_lft', '>=', $parent->_lft)
					->where('_rgt', '<=', $parent->_rgt)
					->pluck('id');
				$query->whereHas('albums', function ($q) use ($descendant_ids): void {
					$q->whereIn('albums.id', $descendant_ids);
				});
			} else {
				$query->whereHas('albums', function ($q) use ($album_id): void {
					$q->where('albums.id', $album_id);
				});
			}
		}

		$query->chunkById(self::CHUNK_SIZE, function (Collection $photos) use ($renamer, &$changes): void {
			foreach ($photos as $photo) {
				$new_title = $renamer->handle($photo->title);
				if ($new_title !== $photo->title) {
					$changes[] = new RenamerPreviewResource($photo->id, $photo->title, $new_title);
				}
			}
		});

		return collect($changes);
	}
}
