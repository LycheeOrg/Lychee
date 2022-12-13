<?php

namespace App\Http\Requests\Users;

use App\Http\Requests\BaseApiRequest;
use App\Http\Requests\Contracts\HasUser;
use App\Http\Requests\Contracts\RequestAttribute;
use App\Http\Requests\Traits\HasUserTrait;
use App\Models\User;
use App\Policies\UserPolicy;
use App\Rules\IntegerIDRule;
use Illuminate\Support\Facades\Gate;

class DeleteUserRequest extends BaseApiRequest implements HasUser
{
	use HasUserTrait;

	/**
	 * {@inheritDoc}
	 */
	public function authorize(): bool
	{
		return Gate::check(UserPolicy::CAN_CREATE_OR_EDIT_OR_DELETE, [User::class]);
	}

	/**
	 * {@inheritDoc}
	 */
	public function rules(): array
	{
		return [
			RequestAttribute::ID_ATTRIBUTE => ['required', new IntegerIDRule(false)],
		];
	}

	/**
	 * {@inheritDoc}
	 */
	protected function processValidatedValues(array $values, array $files): void
	{
		$this->user2 = User::query()->findOrFail($values[RequestAttribute::ID_ATTRIBUTE]);
	}
}
