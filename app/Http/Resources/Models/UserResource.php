<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Resources\Models;

use App\Enum\UserSharedAlbumsVisibility;
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
	public bool $is_ldap;
	public UserSharedAlbumsVisibility $shared_albums_visibility;

	public function __construct(?User $user)
	{
		$this->id = $user?->id;
		$this->has_token = $user?->token !== null;
		$this->username = $user?->username;
		$this->email = $user?->email;
		$this->is_ldap = $user?->is_ldap ?? false;
		$this->shared_albums_visibility = $user?->shared_albums_visibility ?? UserSharedAlbumsVisibility::DEFAULT;
	}
}
