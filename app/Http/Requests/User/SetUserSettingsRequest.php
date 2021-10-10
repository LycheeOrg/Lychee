<?php

namespace App\Http\Requests\User;

use App\Http\Requests\Contracts\HasUserID;
use App\Http\Requests\Traits\HasUserIDTrait;
use App\Rules\ModelIDRule;

class SetUserSettingsRequest extends AbstractUserRequest implements HasUserID
{
	use HasUserIDTrait;

	const ID_ATTRIBUTE = 'id';

	/**
	 * {@inheritDoc}
	 */
	public function rules(): array
	{
		$rules = parent::rules();
		$rules[self::ID_ATTRIBUTE] = ['required', new ModelIDRule(false)];

		return $rules;
	}

	/**
	 * {@inheritDoc}
	 */
	protected function processValidatedValues(array $values, array $files): void
	{
		parent::processValidatedValues($values, $files);
		$this->userID = intval($values[self::ID_ATTRIBUTE]);
	}
}
