<?php

namespace App\Http\Requests\User;

use App\Http\Requests\AbstractEmptyRequest;
use App\Models\User;
use App\Policies\UserPolicy;
use Illuminate\Support\Facades\Gate;

class UserSettingsRequest extends AbstractEmptyRequest
{
	public function authorize(): bool
	{
		return Gate::check(UserPolicy::CAN_EDIT_OWN_SETTINGS, User::class);
	}
}