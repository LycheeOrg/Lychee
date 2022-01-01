<?php

namespace App\Http\Requests\View;

use App\Http\Requests\BaseApiRequest;
use App\Http\Requests\Contracts\HasPhotoID;
use App\Rules\IntegerIDRule;
use App\Rules\OrRule;
use App\Rules\RandomIDRule;

class GetPhotoViewRequest extends BaseApiRequest implements HasPhotoID
{
	protected string $photoID;

	/**
	 * {@inheritDoc}
	 */
	public function authorize(): bool
	{
		return $this->authorizePhotoVisible($this->photoID);
	}

	/**
	 * {@inheritDoc}
	 */
	public function rules(): array
	{
		// We need the OR rule, because we also must support legacy, integer IDs.
		return [
			'p' => [
				'required',
				new OrRule([
					new RandomIDRule(false),
					new IntegerIDRule(false),
				]),
			],
		];
	}

	/**
	 * {@inheritDoc}
	 */
	protected function processValidatedValues(array $values, array $files): void
	{
		$this->photoID = $values[HasPhotoID::PHOTO_ID_ATTRIBUTE];
	}

	/**
	 * @return string
	 */
	public function photoID(): string
	{
		return $this->photoID;
	}
}
