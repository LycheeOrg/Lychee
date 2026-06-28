<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Jobs;

use App\Models\Face;
use App\Models\Person;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * Recompute the stats of a person (face_count and photo_count) based on the current state of the database.
 */
class RecomputePersonStatsJob implements ShouldQueue
{
	use Dispatchable;
	use InteractsWithQueue;
	use Queueable;
	use SerializesModels;

	/**
	 * @param array $person_ids The ID of the persons to recompute
	 */
	public function __construct(
		public array $person_ids,
	) {
	}

	/**
	 * Execute the job.
	 *
	 * @return void
	 */
	public function handle(): void
	{
		foreach ($this->person_ids as $person_id) {
			$face_count = Face::where('person_id', '=', $person_id)
				->where('is_dismissed', '=', false)
				->count();
			$photo_count = Face::where('person_id', '=', $person_id)
				->where('is_dismissed', '=', false)
				->distinct('photo_id')
				->count('photo_id');
			if ($face_count > 0 && $photo_count > 0) {
				Person::where('id', '=', $person_id)->update([
					'face_count' => $face_count,
					'photo_count' => $photo_count,
				]);
				continue;
			}
			if ($face_count === 0 && $photo_count === 0) {
				$this->deletePerson($person_id);
				continue;
			}

			// Now the problematic cases: either face_count or photo_count is zero, but not both. This is inconsistent and should be investigated.
			if ($face_count === 0 && $photo_count > 0) {
				Log::error("Person {$person_id} has no faces but {$photo_count} photos. This is inconsistent and should be investigated.");
			}
			if ($face_count > 0 && $photo_count === 0) {
				Log::error("Person {$person_id} has {$face_count} faces but no photos. This is inconsistent and should be investigated.");
			}
		}
	}

	private function deletePerson(string $id)
	{
		// Clean up the faces first.
		$face_ids = Face::where('person_id', '=', $id)->select('id')->pluck('id')->all();
		Face::where('person_id', '=', $id)->delete();

		// then the person itself.
		Person::where('id', '=', $id)->delete();

		if (count($face_ids) === 0) {
			return;
		}
		DeleteFaceEmbeddingsJob::dispatch($face_ids);
	}
}
