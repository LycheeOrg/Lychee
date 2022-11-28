<?php

namespace App\Http\Requests\Photo;

use App\Http\Requests\BaseApiRequest;
use App\Http\Requests\Contracts\HasPhotos;
use App\Http\Requests\Contracts\HasTitle;
use App\Http\Requests\Traits\Authorize\AuthorizeCanEditPhotosTrait;
use App\Http\Requests\Traits\HasPhotosTrait;
use App\Http\Requests\Traits\HasTitleTrait;
use App\Models\Photo;
use App\Rules\RandomIDRule;
use App\Rules\TitleRule;

class SetPhotosTitleRequest extends BaseApiRequest implements HasPhotos, HasTitle
{
	use HasPhotosTrait;
	use HasTitleTrait;
	use AuthorizeCanEditPhotosTrait;

	/**
	 * {@inheritDoc}
	 */
	public function rules(): array
	{
		return [
			HasPhotos::PHOTO_IDS_ATTRIBUTE => 'required|array|min:1',
			HasPhotos::PHOTO_IDS_ATTRIBUTE . '.*' => ['required', new RandomIDRule(false)],
			HasTitle::TITLE_ATTRIBUTE => ['required', new TitleRule()],
		];
	}

	/**
	 * {@inheritDoc}
	 */
	protected function processValidatedValues(array $values, array $files): void
	{
		$this->photos = Photo::query()->findOrFail($values[HasPhotos::PHOTO_IDS_ATTRIBUTE]);
		$this->title = $values[HasTitle::TITLE_ATTRIBUTE];
	}
}
