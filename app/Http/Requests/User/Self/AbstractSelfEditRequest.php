<?php

namespace App\Http\Requests\User\Self;

use App\Http\Requests\BaseApiRequest;
use App\Models\User;
use App\Policies\UserPolicy;
use Illuminate\Support\Facades\Gate;

abstract class AbstractSelfEditRequest extends BaseApiRequest
{
	public function authorize(): bool
	{
		return Gate::check(UserPolicy::CAN_EDIT_OWN_SETTINGS, User::class);
	}
}
