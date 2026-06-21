<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Observers;

use App\Models\Face;
use App\Models\Person;
use App\Models\Photo;
use Illuminate\Support\Facades\DB;

/**
 * Eloquent observer for the Face model.
 *
 * Maintains the denormalized counter columns:
 *   - photos.face_count        (non-dismissed faces on a photo)
 *   - persons.face_count       (non-dismissed faces assigned to a person)
 *   - persons.photo_count      (distinct photos with non-dismissed faces for a person)
 *
 * All counter mutations are wrapped in a DB transaction to keep the
 * counters consistent even when a batch of faces is updated.
 */
class FaceObserver
{
	/**
	 * Handle the Face "created" event.
	 * Increments photo.face_count when the new face is not dismissed.
	 * Also updates person counters when the face is assigned to a person.
	 */
	public function created(Face $face): void
	{
		if ($face->is_dismissed) {
			return;
		}

		DB::transaction(function () use ($face): void {
			Photo::where('id', '=', $face->photo_id)->increment('face_count');

			if ($face->person_id !== null) {
				Person::where('id', '=', $face->person_id)->increment('face_count');
				$this->recountPersonPhotos($face->person_id);
			}
		});
	}

	/**
	 * Handle the Face "updated" event.
	 * Compares old vs new values of is_dismissed and person_id,
	 * then applies the appropriate counter mutations.
	 */
	public function updated(Face $face): void
	{
		$old_dismissed = $face->getOriginal('is_dismissed') === true || $face->getOriginal('is_dismissed') === 1;
		$new_dismissed = $face->is_dismissed;
		$old_person_id = $face->getOriginal('person_id');
		$new_person_id = $face->person_id;

		$dismissed_changed = $old_dismissed !== $new_dismissed;
		$person_changed = $old_person_id !== $new_person_id;

		if (!$dismissed_changed && !$person_changed) {
			return;
		}

		DB::transaction(function () use ($face, $old_dismissed, $new_dismissed, $old_person_id, $new_person_id, $dismissed_changed, $person_changed): void {
			// ── photo.face_count ─────────────────────────────────────────
			if ($dismissed_changed) {
				if ($new_dismissed) {
					// face was just dismissed → remove from photo count
					Photo::where('id', '=', $face->photo_id)->decrement('face_count');
				} else {
					// face was just undismissed → add to photo count
					Photo::where('id', '=', $face->photo_id)->increment('face_count');
				}
			}

			// ── person counters ───────────────────────────────────────────
			// Determine the effective "was counting for person" state before update.
			$was_counted_for_old_person = !$old_dismissed && $old_person_id !== null;
			$is_counted_for_new_person = !$new_dismissed && $new_person_id !== null;

			if ($person_changed) {
				// Decrement old person (if it was counted)
				if ($was_counted_for_old_person) {
					Person::where('id', '=', $old_person_id)->decrement('face_count');
					$this->recountPersonPhotos($old_person_id);
				}

				// Increment new person (if it should now count)
				if ($is_counted_for_new_person) {
					Person::where('id', '=', $new_person_id)->increment('face_count');
					$this->recountPersonPhotos($new_person_id);
				}
			} elseif ($dismissed_changed && $old_person_id !== null) {
				// person_id unchanged but is_dismissed flipped
				if ($new_dismissed) {
					// dismissed → decrement
					Person::where('id', '=', $old_person_id)->decrement('face_count');
				} else {
					// undismissed → increment
					Person::where('id', '=', $old_person_id)->increment('face_count');
				}
				$this->recountPersonPhotos($old_person_id);
			}
		});
	}

	/**
	 * Handle the Face "deleted" event.
	 * Decrements photo.face_count and person counters when the deleted face was active.
	 */
	public function deleted(Face $face): void
	{
		if ($face->is_dismissed) {
			return;
		}

		DB::transaction(function () use ($face): void {
			Photo::where('id', '=', $face->photo_id)->decrement('face_count');

			if ($face->person_id !== null) {
				Person::where('id', '=', $face->person_id)->decrement('face_count');
				$this->recountPersonPhotos($face->person_id);
			}
		});
	}

	/**
	 * Recount and persist the photo_count for the given person.
	 * photo_count is the number of distinct photos with non-dismissed faces for the person.
	 */
	private function recountPersonPhotos(string $person_id): void
	{
		$count = Face::where('person_id', '=', $person_id)
			->where('is_dismissed', '=', false)
			->distinct('photo_id')
			->count('photo_id');

		Person::where('id', '=', $person_id)->update(['photo_count' => $count]);
	}
}
