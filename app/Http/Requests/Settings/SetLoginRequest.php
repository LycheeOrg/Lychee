<?php

namespace App\Http\Requests\Settings;

use App\Auth\Authorization;
use App\Http\Requests\Session\LoginRequest;
use Illuminate\Auth\Access\AuthorizationException;

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

	/**
	 * Handle a failed authorization attempt.
	 *
	 * @return void
	 *
	 * @throws \Illuminate\Auth\Access\AuthorizationException
	 */
	protected function failedAuthorization(): void
	{
		throw new AuthorizationException('Admin user is already registered');
	}
}