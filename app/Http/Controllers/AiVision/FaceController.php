<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Controllers\AiVision;

use App\Factories\PersonFactory;
use App\Http\Requests\Face\AssignFaceRequest;
use App\Http\Requests\Face\BatchFaceRequest;
use App\Http\Requests\Face\DestroyDismissedFacesRequest;
use App\Http\Requests\Face\ToggleDismissedRequest;
use App\Http\Resources\Models\FaceResource;
use App\Jobs\DeleteFaceEmbeddingsJob;
use App\Models\Face;
use App\Models\Person;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Storage;

/**
 * Controller for Face assignment, dismiss, and cleanup operations.
 */
class FaceController extends Controller
{
	/**
	 * Assign a face to an existing person or create a new person.
	 * If both person_id and new_person_name are absent/null, unassign the face (set person_id = NULL).
	 *
	 * POST /Face/{id}/assign
	 *
	 * @return FaceResource
	 */
	public function assign(AssignFaceRequest $request, string $id, PersonFactory $person_factory): FaceResource
	{
		$face = $request->face();

		if ($request->person_id === null && trim($request->new_person_name ?? '') === '') {
			// Unassign: return face to unassigned pool
			$face->person_id = null;
		} else {
			$person = $person_factory->findOrCreate($request->person_id, $request->new_person_name);
			$face->person_id = $person->id;
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
	public function toggleDismissed(ToggleDismissedRequest $request, string $id): FaceResource
	{
		$face = $request->face();
		$face->is_dismissed = !$face->is_dismissed;
		$face->save();

		return FaceResource::fromModel($face->load(['suggestions.suggestedFace.person', 'person']));
	}

	/**
	 * Batch face operations: unassign all selected faces, or assign them to an existing/new person.
	 *
	 * POST /Face/batch
	 *
	 * @return array{affected_count: int, person_id: string|null}
	 */
	public function batch(BatchFaceRequest $request, PersonFactory $person_factory): array
	{
		$face_ids = $request->face_ids;

		if ($request->action === 'unassign') {
			$count = Face::whereIn('id', $face_ids)->update(['person_id' => null]);

			return ['affected_count' => $count, 'person_id' => null];
		}

		// action === 'assign'
		if ($request->person_id !== null) {
			$person = Person::findOrFail($request->person_id);
		} else {
			$person = $person_factory->findOrCreate(null, $request->new_person_name ?? '');
		}

		$count = Face::whereIn('id', $face_ids)->update(['person_id' => $person->id]);

		return ['affected_count' => $count, 'person_id' => $person->id];
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
		$face_ids = $dismissed_faces->pluck('id')->all();
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

		if ($face_ids !== []) {
			DeleteFaceEmbeddingsJob::dispatch($face_ids);
		}

		return ['deleted_count' => $count];
	}
}
