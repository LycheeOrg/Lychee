<?php

namespace App\Http\Requests\Photo;

use App\Http\Requests\BaseApiRequest;
use App\Http\Requests\Contracts\HasPhotoIDs;
use App\Http\Requests\Contracts\HasTags;
use App\Http\Requests\Traits\HasPhotoIDsTrait;
use App\Http\Requests\Traits\HasTagsTrait;
use App\Rules\RandomIDListRule;
use App\Rules\TagsRule;

class SetPhotosTagsRequest extends BaseApiRequest implements HasPhotoIDs, HasTags
{
	use HasPhotoIDsTrait;
	use HasTagsTrait;

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
			HasPhotoIDs::PHOTO_IDS_ATTRIBUTE => ['required', new RandomIDListRule()],
			HasTags::TAGS_ATTRIBUTE => ['present', new TagsRule(true)],
		];
	}

	/**
	 * {@inheritDoc}
	 */
	protected function processValidatedValues(array $values, array $files): void
	{
		$this->photoIDs = explode(',', $values[HasPhotoIDs::PHOTO_IDS_ATTRIBUTE]);
		$this->tags = $values[HasTags::TAGS_ATTRIBUTE];
	}
}