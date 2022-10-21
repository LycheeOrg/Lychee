<?php

namespace App\Http\Requests\User;

use App\Http\Requests\Contracts\HasUser;
use App\Http\Requests\Traits\HasUserTrait;
use App\Models\User;
use App\Rules\IntegerIDRule;

class SetUserSettingsRequest extends AbstractUsernamePasswordRequest implements HasUser
{
	use HasUserTrait;

	public const ID_ATTRIBUTE = 'id';

	/**
	 * {@inheritDoc}
	 */
	public function rules(): array
	{
		$rules = parent::rules();
		$rules[self::ID_ATTRIBUTE] = ['required', new IntegerIDRule(false)];

		return $rules;
	}

	/**
	 * {@inheritDoc}
	 */
	protected function processValidatedValues(array $values, array $files): void
	{
		parent::processValidatedValues($values, $files);
		$this->user2 = User::query()->findOrFail($values[self::ID_ATTRIBUTE]);
	}
}
