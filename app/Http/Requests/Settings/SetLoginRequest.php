<?php

namespace App\Http\Requests\Settings;

use App\Auth\Authorization;
use App\Http\Requests\Session\LoginRequest;

class SetLoginRequest extends LoginRequest
{
	/**
	 * Determines if the user is authorized to make this request.
	 */
	public function authorize(): bool
	{
		// Only use this route if there is no admin.
		return Authorization::isAdminNotRegistered();
	}
}