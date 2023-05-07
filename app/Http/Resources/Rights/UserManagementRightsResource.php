<?php

namespace App\Http\Resources\Rights;

use App\Models\User;
use App\Policies\UserPolicy;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Gate;

/**
 * Data Transfer Object (DTO) to transmit the rights of an user on different user accounts.
 */
class UserManagementRightsResource extends JsonResource
{
	public function __construct()
	{
		// Laravel applies a shortcut when this value === null but not when it is something else.
		parent::__construct('must_not_be_null');
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
			'can_create' => Gate::check(UserPolicy::CAN_CREATE_OR_EDIT_OR_DELETE, [User::class]),
			'can_list' => Gate::check(UserPolicy::CAN_LIST, [User::class]),
			'can_edit' => Gate::check(UserPolicy::CAN_CREATE_OR_EDIT_OR_DELETE, [User::class]),
			'can_delete' => Gate::check(UserPolicy::CAN_CREATE_OR_EDIT_OR_DELETE, [User::class]),
		];
	}
}
