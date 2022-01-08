<?php

namespace App\Http\Requests\User;

use App\Http\Requests\Contracts\HasUserID;
use App\Http\Requests\Traits\HasUserIDTrait;
use App\Rules\IntegerIDRule;

class SetUserSettingsRequest extends AbstractUserRequest implements HasUserID
{
	use HasUserIDTrait;

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
		$this->userID = $values[self::ID_ATTRIBUTE];
	}
}
