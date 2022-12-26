<?php

namespace App\Http\Resources\Rights;

use App\Http\Resources\JsonResource;
use App\Models\User;
use App\Policies\UserPolicy;
use Illuminate\Support\Facades\Gate;

class UserRightsResource extends JsonResource
{
	/**
	 * Transform the resource into an array.
	 *
	 * @param \Illuminate\Http\Request $request
	 *
	 * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
	 */
	public function toArray($request)
	{
		return [
			'can_edit' => Gate::check(UserPolicy::CAN_EDIT, [User::class]),
			'can_use_2fa' => Gate::check(UserPolicy::CAN_USE_2FA, [User::class]),
		];
	}
}
