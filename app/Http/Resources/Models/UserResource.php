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
class UserResource extends Data
{
	public ?int $id;
	public ?bool $has_token;
	public ?string $username;
	public ?string $email;

	public function __construct(?User $user)
	{
		$this->id = $user?->id;
		$this->has_token = $user?->token !== null;
		$this->username = $user?->username;
		$this->email = $user?->email;
	}
}
