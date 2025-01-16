<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Http\Resources\Models;

use App\Models\User;
use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript()]
class LightUserResource extends Data
{
	public int $id;
	public string $username;

	public function __construct(User $user)
	{
		$this->id = $user->id;
		$this->username = $user->username;
	}

	public static function fromModel(User $c): LightUserResource
	{
		return new self($c);
	}
}
