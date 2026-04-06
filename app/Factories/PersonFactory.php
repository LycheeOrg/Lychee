<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Factories;

use App\Models\Person;
use App\Repositories\ConfigManager;

class PersonFactory
{
	public function __construct(
		protected readonly ConfigManager $config_manager,
	) {
	}

	public function findOrCreate(?string $id, string $name): Person
	{
		if ($id !== null) {
			return Person::findOrFail($id);
		}

		$person = new Person();
		$person->name = $name;
		$person->is_searchable = $this->config_manager->getValueAsBool('ai_vision_face_person_is_searchable_default');
		$person->save();

		return $person;
	}
}