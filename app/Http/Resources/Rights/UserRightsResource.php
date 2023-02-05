<?php

namespace App\Http\Resources\Rights;

use App\Models\User;
use App\Policies\UserPolicy;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Gate;

/**
 * Data Transfer Object (DTO) to transmit the rights of an user.
 */
class UserRightsResource extends JsonResource
{
	public function __construct()
	{
		parent::__construct(null);
	}

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
