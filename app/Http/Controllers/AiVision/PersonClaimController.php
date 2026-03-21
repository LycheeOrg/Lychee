<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Controllers\AiVision;

use App\Http\Requests\Person\ClaimPersonRequest;
use App\Http\Requests\Person\MergePersonRequest;
use App\Http\Resources\Models\PersonResource;
use App\Models\Face;
use App\Models\Person;
use App\Repositories\ConfigManager;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;

/**
 * Controller for Person claim, admin override, and merge actions.
 */
class PersonClaimController extends Controller
{
	/**
	 * Claim a Person by linking it to the authenticated user.
	 * Admin can force-claim (overriding existing link).
	 * Non-admin cannot claim if ai_vision_face_allow_user_claim is false.
	 *
	 * @return PersonResource
	 */
	public function claim(ClaimPersonRequest $_request, string $id): PersonResource
	{
		/** @var \App\Models\User $user */
		$user = Auth::user();
		$person = Person::findOrFail($id);

		if (!$user->may_administrate) {
			// Check if user claims are allowed
			if (app(ConfigManager::class)->getValueAsString('ai_vision_face_allow_user_claim') !== '1') {
				abort(403, 'User claims are not permitted by the administrator.');
			}

			// Non-admin: conflict if already claimed by another user
			if ($person->user_id !== null && $person->user_id !== $user->id) {
				abort(409, 'This person is already claimed by another user.');
			}

			// Non-admin: ensure user doesn't already have a different person
			$existing = Person::where('user_id', '=', $user->id)->where('id', '!=', $person->id)->first();
			if ($existing !== null) {
				abort(409, 'You have already claimed a different person.');
			}
		} else {
			// Admin force-claim: clear any existing link for this user
			Person::where('user_id', '=', $user->id)->where('id', '!=', $person->id)->update(['user_id' => null]);
		}

		$person->user_id = $user->id;
		$person->save();

		return PersonResource::fromModel($person->fresh());
	}

	/**
	 * Unclaim a Person — remove the user_id link.
	 * Only the linked user or admin can unclaim.
	 */
	public function unclaim(ClaimPersonRequest $_request, string $id): void
	{
		/** @var \App\Models\User $user */
		$user = Auth::user();
		$person = Person::findOrFail($id);

		if (!$user->may_administrate && $person->user_id !== $user->id) {
			abort(403, 'Only the linked user or an admin can unclaim this person.');
		}

		$person->user_id = null;
		$person->save();
	}

	/**
	 * Merge source Person into target Person.
	 * All Face records from source are reassigned to target; source is deleted.
	 *
	 * URL: POST /Person/{id}/merge where {id} is the TARGET person (kept).
	 * Body: source_person_id = the person to be destroyed.
	 *
	 * @return PersonResource
	 */
	public function merge(MergePersonRequest $request, string $id): PersonResource
	{
		$target = Person::findOrFail($id);
		$source = Person::findOrFail($request->sourcePersonId());

		// Reassign all faces from source to target
		Face::where('person_id', '=', $source->id)->update(['person_id' => $target->id]);

		// Delete source person
		$source->delete();

		return PersonResource::fromModel($target->fresh());
	}
}
