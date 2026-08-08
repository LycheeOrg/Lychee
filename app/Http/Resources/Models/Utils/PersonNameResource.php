<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Resources\Models\Utils;

use App\Models\Person;
use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript()]
class PersonNameResource extends Data
{
	public string $id;
	public string $name;

	public function __construct(Person $person)
	{
		$this->id = $person->id;
		$this->name = $person->name;
	}
}
