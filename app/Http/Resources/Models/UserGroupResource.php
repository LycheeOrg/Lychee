<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Http\Resources\Models;

use App\Enum\UserGroupRole;
use App\Exceptions\UnauthorizedException;
use App\Http\Resources\Rights\UserGroupRightResource;
use App\Models\UserGroup;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\LiteralTypeScriptType;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript()]
class UserGroupResource extends Data
{
	public int $id;
	public string $name;
	public string $description;
	/** @var Collection<int,UserMemberGroupResource> */
	#[LiteralTypeScriptType('App.Http.Resources.Models.UserMemberGroupResource[]')]
	public Collection $members;
	public UserGroupRightResource $rights;

	public function __construct(UserGroup $group)
	{
		$this->id = $group->id;
		$this->name = $group->name;
		$this->description = $group->description ?? '';
		$this->members = resolve(Collection::class);
		$this->rights = new UserGroupRightResource($group);

		// We only show the members if the user is authenticated
		// and either has administrative rights or is a member of the group.
		$user = Auth::user() ?? throw new UnauthorizedException();
		if ($user->may_administrate || $group->users->some(fn ($u) => $u->id === $user->id)) {
			$this->members = $group->users->map(function ($user) {
				return new UserMemberGroupResource(
					id: $user->id,
					username: $user->username,
					// @phpstan-ignore-next-line
					role: UserGroupRole::from($user->pivot->role)
				);
			});
		}
	}
}
