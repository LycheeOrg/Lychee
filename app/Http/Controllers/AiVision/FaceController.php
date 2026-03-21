<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Controllers\AiVision;

use App\Http\Requests\Face\AssignFaceRequest;
use App\Http\Requests\Face\DestroyDismissedFacesRequest;
use App\Http\Requests\Face\ToggleDismissedRequest;
use App\Http\Resources\Models\FaceResource;
use App\Models\Face;
use App\Models\Person;
use App\Repositories\ConfigManager;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

/**
 * Controller for Face assignment, dismiss, and cleanup operations.
 */
class FaceController extends Controller
{
	/**
	 * Assign a face to an existing person or create a new person.
	 *
	 * POST /Face/{id}/assign
	 *
	 * @return FaceResource
	 */
	public function assign(AssignFaceRequest $request, string $id): FaceResource
	{
		$face = Face::findOrFail($id);

		if ($request->personId() !== null) {
			$face->person_id = $request->personId();
		} elseif ($request->newPersonName() !== null) {
			$is_searchable_default = app(ConfigManager::class)->getValueAsString('ai_vision_face_person_is_searchable_default') === '1';
			$person = new Person();
			$person->name = $request->newPersonName();
			$person->is_searchable = $is_searchable_default;
			$person->save();
			$face->person_id = $person->id;
		} else {
			abort(422, 'Either person_id or new_person_name must be provided.');
		}

		$face->save();

		return FaceResource::fromModel($face->load(['suggestions.suggestedFace.person', 'person']));
	}

	/**
	 * Toggle the is_dismissed flag on a face.
	 * Only the photo owner or admin can dismiss/undismiss.
	 *
	 * PATCH /Face/{id}
	 *
	 * @return FaceResource
	 */
	public function toggleDismissed(ToggleDismissedRequest $_request, string $id): FaceResource
	{
		$face = Face::with('photo')->findOrFail($id);
		$user = Auth::user();

		// Check ownership: photo owner or admin
		if (!($user?->may_administrate === true) && $face->photo->owner_id !== $user?->id) {
			abort(403, 'Only the photo owner or an admin can dismiss faces.');
		}

		$face->is_dismissed = !$face->is_dismissed;
		$face->save();

		return FaceResource::fromModel($face->load(['suggestions.suggestedFace.person', 'person']));
	}

	/**
	 * Hard-delete all dismissed faces and remove their crop files.
	 * Admin-only.
	 *
	 * DELETE /Face/dismissed
	 *
	 * @return array{deleted_count: int}
	 */
	public function destroyDismissed(DestroyDismissedFacesRequest $_request): array
	{
		$dismissed_faces = Face::where('is_dismissed', '=', true)->get();
		$count = 0;

		foreach ($dismissed_faces as $face) {
			// Delete crop file
			if ($face->crop_token !== null) {
				$tok = $face->crop_token;
				$path = 'faces/' . substr($tok, 0, 2) . '/' . substr($tok, 2, 2) . '/' . $tok . '.jpg';
				Storage::disk('images')->delete($path);
			}
			$face->delete();
			$count++;
		}

		return ['deleted_count' => $count];
	}
}
