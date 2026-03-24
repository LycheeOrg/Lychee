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
use App\Jobs\DeleteFaceEmbeddingsJob;
use App\Models\Face;
use App\Models\Person;
use App\Repositories\ConfigManager;
use Illuminate\Routing\Controller;
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
		$face = $request->face();

		if ($request->person() !== null) {
			$face->person_id = $request->person()->id;
		} else {
			$is_searchable_default = app(ConfigManager::class)->getValueAsBool('ai_vision_face_person_is_searchable_default');
			$person = new Person();
			$person->name = $request->newPersonName();
			$person->is_searchable = $is_searchable_default;
			$person->save();
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
