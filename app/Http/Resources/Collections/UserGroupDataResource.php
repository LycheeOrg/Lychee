<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Resources\Collections;

use App\Http\Resources\Models\UserGroupResource;
use App\Models\UserGroup;
use App\Policies\UserGroupPolicy;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Gate;
use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\LiteralTypeScriptType;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

/**
 * Data Transfer Object (DTO) to transmit the top albums to the client.
 *
 * This DTO differentiates between albums which are owned by the user and
 * "shared" albums which the user does not own, but is allowed to see.
 * The term "shared album" might be a little misleading here.
 * Albums which are owned by the user himself may also be shared (with
 * other users.)
 * Actually, in this context "shared albums" means "foreign albums".
 */
#[TypeScript()]
class UserGroupDataResource extends Data
{
	/** @var Collection<int,UserGroupResource> */
	#[LiteralTypeScriptType('App.Http.Resources.Models.UserGroupResource[]')]
	public Collection $user_groups;
	public bool $can_create_delete_user_groups;

	/**
	 * @param Collection<int,UserGroup> $user_groups
	 */
	public function __construct(Collection $user_groups)
	{
		$this->user_groups = $user_groups->map(fn (UserGroup $ug) => new UserGroupResource($ug));
		$this->can_create_delete_user_groups = Gate::check(UserGroupPolicy::CAN_CREATE, [UserGroup::class]);
	}
}
