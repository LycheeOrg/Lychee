<?php

namespace App\Http\Requests\Legacy;

use App\Exceptions\UnauthorizedException;
use App\Http\Requests\Session\LoginRequest;
use App\Legacy\AdminAuthentication;

class SetAdminLoginRequest extends LoginRequest
{
	/**
	 * Determines if the user is authorized to make this request.
	 */
	public function authorize(): bool
	{
		// Only use this route if there is no admin.
		return AdminAuthentication::isAdminNotRegistered();
	}

	/**
	 * Handle a failed authorization attempt.
	 *
	 * @return void
	 *
	 * @throws UnauthorizedException
	 */
	protected function failedAuthorization(): void
	{
		throw new UnauthorizedException('Admin user is already registered');
	}
}