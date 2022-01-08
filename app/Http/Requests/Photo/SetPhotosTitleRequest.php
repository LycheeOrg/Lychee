<?php

namespace App\Http\Requests\Photo;

use App\Http\Requests\BaseApiRequest;
use App\Http\Requests\Contracts\HasPhotoIDs;
use App\Http\Requests\Contracts\HasTitle;
use App\Http\Requests\Traits\HasPhotoIDsTrait;
use App\Http\Requests\Traits\HasTitleTrait;
use App\Rules\RandomIDRule;
use App\Rules\TitleRule;

class SetPhotosTitleRequest extends BaseApiRequest implements HasPhotoIDs, HasTitle
{
	use HasPhotoIDsTrait;
	use HasTitleTrait;

	/**
	 * {@inheritDoc}
	 */
	public function authorize(): bool
	{
		return $this->authorizePhotoWrite($this->photoIDs);
	}

	/**
	 * {@inheritDoc}
	 */
	public function rules(): array
	{
		return [
			HasPhotoIDs::PHOTO_IDS_ATTRIBUTE => 'required|array|min:1',
			HasPhotoIDs::PHOTO_IDS_ATTRIBUTE . '*' => ['required', new RandomIDRule(false)],
			HasTitle::TITLE_ATTRIBUTE => ['required', new TitleRule()],
		];
	}

	/**
	 * {@inheritDoc}
	 */
	protected function processValidatedValues(array $values, array $files): void
	{
		$this->photoIDs = $values[HasPhotoIDs::PHOTO_IDS_ATTRIBUTE];
		$this->title = $values[HasTitle::TITLE_ATTRIBUTE];
	}
}
