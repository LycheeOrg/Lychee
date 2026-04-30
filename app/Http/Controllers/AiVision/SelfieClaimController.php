<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Controllers\AiVision;

use App\Http\Requests\Person\SelfieClaimRequest;
use App\Http\Resources\Models\PersonResource;
use App\Models\Face;
use App\Models\Person;
use App\Repositories\ConfigManager;
use App\Services\Image\FacialRecognitionService;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use function Safe\unlink;

/**
 * Controller for selfie-based Person claim.
 * Forwards the selfie image to the Python service /match endpoint,
 * then links the matched Person to the authenticated user.
 */
class SelfieClaimController extends Controller
{
	/**
	 * Upload a selfie to find and claim the matching Person.
	 *
	 * POST /Person/claim-by-selfie
	 *
	 * @return PersonResource
	 */
	public function claimBySelfie(SelfieClaimRequest $request, ConfigManager $config_manager, FacialRecognitionService $facial_recognition_service): PersonResource
	{
		/** @var \App\Models\User $user */
		$user = Auth::user();

		if (!$facial_recognition_service->isConfigured()) {
			abort(503, 'AI Vision service is not configured.');
		}

		$selfie = $request->selfie();

		try {
			$data = $facial_recognition_service->matchSelfie($selfie->getRealPath(), $selfie->getClientOriginalName());
		} catch (\Exception $e) {
			Log::warning('AI Vision selfie match request failed: ' . $e->getMessage());
			abort(503, 'AI Vision service is unavailable.');
		} finally {
			// Discard selfie immediately
			unlink($selfie->getRealPath());
		}

		if ($data === null) {
			abort(503, 'AI Vision service returned an error.');
		}

		/** @var array{matches: array<array{lychee_face_id: string, confidence: float}>} $data */
		$matches = $data['matches'] ?? [];

		if ($matches === []) {
			abort(404, 'No matching person found for the selfie.');
		}

		$threshold = (float) $config_manager->getValueAsString('ai_vision_face_selfie_confidence_threshold');
		$best_match = $matches[0];

		if ($best_match['confidence'] < $threshold) {
			abort(404, 'No matching person found with sufficient confidence.');
		}

		$face = Face::findOrFail($best_match['lychee_face_id']);

		if ($face->person_id === null) {
			abort(404, 'No person associated with the matched face.');
		}

		$person = Person::findOrFail($face->person_id);

		// Check if already claimed by another user
		if ($person->user_id !== null && $person->user_id !== $user->id) {
			abort(409, 'This person is already claimed by another user.');
		}

		// Ensure user doesn't already have a different person linked
		$existing = Person::where('user_id', '=', $user->id)->where('id', '!=', $person->id)->first();
		if ($existing !== null) {
			abort(409, 'You have already claimed a different person.');
		}

		// Check if user claims are allowed
		if (!$user->may_administrate && !$config_manager->getValueAsBool('ai_vision_face_allow_user_claim')) {
			abort(403, 'User claims are not permitted by the administrator.');
		}

		$person->user_id = $user->id;
		$person->save();

		return PersonResource::fromModel($person->fresh());
	}
}
