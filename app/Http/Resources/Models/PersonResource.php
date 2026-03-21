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
	public int $face_count;
	public int $photo_count;
	public ?string $representative_crop_url;

	public function __construct(Person $person)
	{
		$this->id = $person->id;
		$this->name = $person->name;
		$this->user_id = $person->user_id;
		$this->is_searchable = $person->is_searchable;
		$this->face_count = $person->faces()->count();
		$this->photo_count = $person->faces()->distinct('photo_id')->count('photo_id');

		$crop_token = $person->faces()->whereNotNull('crop_token')->value('crop_token');
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
