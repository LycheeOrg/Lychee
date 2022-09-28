<?php

namespace App\Http\Requests\User;

use App\Http\Requests\Contracts\HasUser;
use App\Http\Requests\Traits\HasUserTrait;
use App\Models\User;
use App\Policies\UserPolicy;
use App\Rules\IntegerIDRule;
use Illuminate\Support\Facades\Gate;

class DeleteUserRequest extends AbstractUserRequest implements HasUser
{
	use HasUserTrait;

	public const ID_ATTRIBUTE = 'id';

	/**
	 * {@inheritDoc}
	 */
	public function authorize(): bool
	{
		return Gate::check(UserPolicy::CAN_CREATE_OR_EDIT_OR_DELETE, User::class);
	}

	/**
	 * {@inheritDoc}
	 */
	public function rules(): array
	{
		return [
			self::ID_ATTRIBUTE => ['required', new IntegerIDRule(false)],
		];
	}

	/**
	 * {@inheritDoc}
	 */
	protected function processValidatedValues(array $values, array $files): void
	{
		$this->user2 = User::query()->findOrFail($values[self::ID_ATTRIBUTE]);
	}
}
