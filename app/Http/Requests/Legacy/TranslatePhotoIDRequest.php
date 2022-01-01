<?php

namespace App\Http\Requests\Legacy;

use App\Http\Requests\BaseApiRequest;
use App\Http\Requests\Contracts\HasPhotoID;
use App\Http\Requests\Traits\HasPhotoIDTrait;
use App\Rules\IntegerIDRule;

class TranslatePhotoIDRequest extends BaseApiRequest implements HasPhotoID
{
	use HasPhotoIDTrait;

	/**
	 * {@inheritDoc}
	 */
	public function authorize(): bool
	{
		return true;
	}

	/**
	 * {@inheritDoc}
	 */
	public function rules(): array
	{
		return [
			HasPhotoID::PHOTO_ID_ATTRIBUTE => ['required', new IntegerIDRule(false)],
		];
	}

	/**
	 * {@inheritDoc}
	 */
	protected function processValidatedValues(array $values, array $files): void
	{
		$this->photoID = $values[HasPhotoID::PHOTO_ID_ATTRIBUTE];
	}
}
