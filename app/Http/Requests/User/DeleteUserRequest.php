<?php

namespace App\Http\Requests\User;

use App\Facades\AccessControl;
use App\Http\Requests\BaseApiRequest;
use App\Http\Requests\Contracts\HasUserID;
use App\Http\Requests\Traits\HasUserIDTrait;
use App\Rules\IntegerIDRule;

class DeleteUserRequest extends BaseApiRequest implements HasUserID
{
	use HasUserIDTrait;

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
		return AccessControl::is_admin();
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
		$this->userID = $values[self::ID_ATTRIBUTE];
	}
}
