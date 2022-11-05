<?php

namespace App\Http\Requests\UserManagement;

use App\Http\Requests\AbstractEmptyRequest;
use App\Models\User;
use App\Policies\UserPolicy;
use Illuminate\Support\Facades\Gate;

class ListUserRequest extends AbstractEmptyRequest
{
	/**
	 * {@inheritDoc}
	 */
	public function authorize(): bool
	{
		return Gate::check(UserPolicy::CAN_LIST, User::class);
	}
}
