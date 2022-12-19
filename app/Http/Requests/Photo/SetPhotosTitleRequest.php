<?php

namespace App\Http\Requests\Photo;

use App\Contracts\Http\Requests\HasPhotos;
use App\Contracts\Http\Requests\HasTitle;
use App\Contracts\Http\Requests\RequestAttribute;
use App\Http\Requests\BaseApiRequest;
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
			RequestAttribute::PHOTO_IDS_ATTRIBUTE => 'required|array|min:1',
			RequestAttribute::PHOTO_IDS_ATTRIBUTE . '.*' => ['required', new RandomIDRule(false)],
			RequestAttribute::TITLE_ATTRIBUTE => ['required', new TitleRule()],
		];
	}

	/**
	 * {@inheritDoc}
	 */
	protected function processValidatedValues(array $values, array $files): void
	{
		$this->photos = Photo::query()->findOrFail($values[RequestAttribute::PHOTO_IDS_ATTRIBUTE]);
		$this->title = $values[RequestAttribute::TITLE_ATTRIBUTE];
	}
}
