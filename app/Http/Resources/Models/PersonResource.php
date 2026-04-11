<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Resources\Models;

use App\Models\Person;
use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript()]
class PersonResource extends Data
{
	public string $id;
	public string $name;
	public ?int $user_id;
	public bool $is_searchable;
	public ?string $representative_face_id;
	public int $face_count;
	public int $photo_count;
	public ?string $representative_crop_url;

	public function __construct(Person $person)
	{
		$this->id = $person->id;
		$this->name = $person->name;
		$this->user_id = $person->user_id;
		$this->is_searchable = $person->is_searchable;
		$this->representative_face_id = $person->representative_face_id;
		$this->face_count = $person->face_count;
		$this->photo_count = $person->photo_count;

		// Representative crop: prefer the explicitly pinned face's crop,
		// fall back to the highest-confidence non-dismissed face with a crop.
		$crop_token = null;
		if ($person->representative_face_id !== null) {
			$crop_token = $person->faces()
				->where('id', '=', $person->representative_face_id)
				->whereNotNull('crop_token')
				->value('crop_token');
		}

		if ($crop_token === null) {
			$crop_token = $person->faces()
				->notDismissed()
				->whereNotNull('crop_token')
				->orderByDesc('confidence')
				->value('crop_token');
		}

		if ($crop_token !== null) {
			$this->representative_crop_url = 'uploads/faces/' . substr($crop_token, 0, 2) . '/' . substr($crop_token, 2, 2) . '/' . $crop_token . '.jpg';
		} else {
			$this->representative_crop_url = null;
		}
	}

	public static function fromModel(Person $person): self
	{
		return new self($person);
	}
}
