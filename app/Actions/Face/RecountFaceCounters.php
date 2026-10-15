<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Actions\Face;

use App\Models\Face;
use App\Models\Person;
use App\Models\Photo;

/**
 * Recomputes the denormalized face counters from the faces table:
 *   - photos.face_count        (non-dismissed faces on a photo)
 *   - persons.face_count       (non-dismissed faces assigned to a person)
 *   - persons.photo_count      (distinct photos with non-dismissed faces for a person)
 *
 * Callers invoke it explicitly after writing faces (no Eloquent observer).
 * Counts are recomputed rather than incremented, so the operation is
 * idempotent and the counters can never drift below zero.
 */
class RecountFaceCounters
{
	/**
	 * @param string[] $photo_ids
	 */
	public function forPhotos(array $photo_ids): void
	{
		foreach (array_unique($photo_ids) as $photo_id) {
			$count = Face::where('photo_id', '=', $photo_id)->where('is_dismissed', '=', false)->count();
			Photo::where('id', '=', $photo_id)->update(['face_count' => $count]);
		}
	}

	/**
	 * @param array<string|null> $person_ids null entries are ignored
	 */
	public function forPersons(array $person_ids): void
	{
		foreach (array_unique(array_filter($person_ids, fn ($id) => $id !== null)) as $person_id) {
			$active_faces = Face::where('person_id', '=', $person_id)->where('is_dismissed', '=', false);
			Person::where('id', '=', $person_id)->update([
				'face_count' => (clone $active_faces)->count(),
				'photo_count' => $active_faces->distinct('photo_id')->count('photo_id'),
			]);
		}
	}
}
