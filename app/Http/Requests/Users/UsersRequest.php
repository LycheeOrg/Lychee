<?php

namespace App\Http\Requests\Users;

use App\Http\Requests\BaseApiRequest;
use Illuminate\Support\Facades\Auth;

class UsersRequest extends BaseApiRequest
{
	/**
	 * {@inheritDoc}
	 */
	public function authorize(): bool
	{
		return Auth::check();
	}

	/**
	 * {@inheritDoc}
	 */
	public function rules(): array
	{
		return [];
	}

	/**
	 * {@inheritDoc}
	 */
	protected function processValidatedValues(array $values, array $files): void
	{
	}
}
