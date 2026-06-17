<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Resources\Models;

use App\Http\Resources\Rights\PhotoRightsResource;
use App\Models\Face;
use App\Models\Photo;
use Illuminate\Support\Facades\Auth;
use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\LiteralTypeScriptType;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript()]
class PhotoFacesResource extends Data
{
	/** @var FaceResource[] */
	#[LiteralTypeScriptType('App.Http.Resources.Models.FaceResource[]')]
	public array $faces = [];
	public int $hidden_face_count = 0;
	public PhotoRightsResource $rights;

	public function __construct(Photo $photo)
	{
		$user = Auth::user();
		$is_admin = $user?->may_administrate === true;

		foreach ($photo->faces as $face) {
			/** @var Face $face */
			if ($face->is_dismissed) {
				continue;
			}

			// Unassigned face or searchable person: always include.
			if ($face->person_id === null || ($face->person !== null && $face->person->is_searchable)) {
				$this->faces[] = new FaceResource($face);
				continue;
			}

			// Non-searchable person: visible only to the linked user or admin.
			if ($is_admin || ($user !== null && $face->person?->user_id === $user->id)) {
				$this->faces[] = new FaceResource($face);
			} else {
				$this->hidden_face_count++;
			}
		}

		$this->rights = new PhotoRightsResource($photo->albums->first(), $photo);
	}
}
