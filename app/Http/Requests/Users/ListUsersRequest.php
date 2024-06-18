<?php

declare(strict_types=1);

namespace App\Http\Requests\Users;

use App\Http\Requests\AbstractEmptyRequest;
use App\Models\User;
use App\Policies\UserPolicy;
use Illuminate\Support\Facades\Gate;

class ListUsersRequest extends AbstractEmptyRequest
{
	/**
	 * {@inheritDoc}
	 */
	public function authorize(): bool
	{
		return Gate::check(UserPolicy::CAN_LIST, [User::class]);
	}
}
