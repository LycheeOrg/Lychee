<?php

namespace App\Http\Requests\Photo;

use App\Http\Requests\BaseApiRequest;
use App\Http\Requests\Contracts\HasPhotoID;
use App\Http\Requests\Traits\HasPhotoIDTrait;
use App\Rules\RandomIDRule;
use Illuminate\Validation\Rule;

class RotatePhotoRequest extends BaseApiRequest implements HasPhotoID
{
	use HasPhotoIDTrait;

	public const DIRECTION_ATTRIBUTE = 'direction';

	protected int $direction;

	/**
	 * {@inheritDoc}
	 */
	public function authorize(): bool
	{
		return $this->authorizePhotoWrite([$this->photoID]);
	}

	/**
	 * {@inheritDoc}
	 */
	public function rules(): array
	{
		return [
			HasPhotoID::PHOTO_ID_ATTRIBUTE => ['required', new RandomIDRule(false)],
			self::DIRECTION_ATTRIBUTE => ['required', Rule::in([-1, 1])],
		];
	}

	/**
	 * {@inheritDoc}
	 */
	protected function processValidatedValues(array $values, array $files): void
	{
		$this->photoID = $values[HasPhotoID::PHOTO_ID_ATTRIBUTE];
		$this->direction = intval($values[self::DIRECTION_ATTRIBUTE]);
	}

	public function direction(): int
	{
		return $this->direction;
	}
}
