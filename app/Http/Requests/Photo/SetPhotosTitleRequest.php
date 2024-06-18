<?php

declare(strict_types=1);

namespace App\Http\Requests\Photo;

use App\Contracts\Http\Requests\HasPhotos;
use App\Contracts\Http\Requests\HasTitle;
use App\Contracts\Http\Requests\RequestAttribute;
use App\Http\Requests\BaseApiRequest;
use App\Http\Requests\Traits\Authorize\AuthorizeCanEditPhotosTrait;
use App\Http\Requests\Traits\HasPhotosTrait;
use App\Http\Requests\Traits\HasTitleTrait;
use App\Http\RuleSets\Photo\SetPhotosTitleRuleSet;
use App\Models\Photo;

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
		return SetPhotosTitleRuleSet::rules();
	}

	/**
	 * {@inheritDoc}
	 */
	protected function processValidatedValues(array $values, array $files): void
	{
		/** @var array<int,string> $photosIDs */
		$photosIDs = $values[RequestAttribute::PHOTO_IDS_ATTRIBUTE];
		$this->photos = Photo::query()->findOrFail($photosIDs);
		$this->title = $values[RequestAttribute::TITLE_ATTRIBUTE];
	}
}
