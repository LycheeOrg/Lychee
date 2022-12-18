<?php

namespace App\Http\Requests\User;

use App\Contracts\Http\Requests\HasUser;
use App\Http\Requests\BaseApiRequest;
use App\Http\Requests\Traits\HasUserTrait;
use App\Models\User;
use App\Policies\UserPolicy;
use App\Rules\IntegerIDRule;
use Illuminate\Support\Facades\Gate;

class DeleteUserRequest extends BaseApiRequest implements HasUser
{
	use HasUserTrait;

	public const ID_ATTRIBUTE = 'id';

	/**
	 * {@inheritDoc}
	 */
	public function authorize(): bool
	{
		// This should always return true, because we already check that the
		// request is made by an admin during authentication (see
		// `routes/web.php`).
		// But better safe than sorry.
		return Gate::check(UserPolicy::IS_ADMIN);
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
