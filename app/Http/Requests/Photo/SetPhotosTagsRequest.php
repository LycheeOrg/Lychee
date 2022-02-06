<?php

namespace App\Http\Requests\Photo;

use App\Http\Requests\BaseApiRequest;
use App\Http\Requests\Contracts\HasPhotoIDs;
use App\Http\Requests\Contracts\HasTags;
use App\Http\Requests\Traits\HasPhotoIDsTrait;
use App\Http\Requests\Traits\HasTagsTrait;
use App\Rules\RandomIDRule;

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
			HasPhotoIDs::PHOTO_IDS_ATTRIBUTE => 'required|array|min:1',
			HasPhotoIDs::PHOTO_IDS_ATTRIBUTE . '.*' => ['required', new RandomIDRule(false)],
			HasTags::TAGS_ATTRIBUTE => 'present|array',
			HasTags::TAGS_ATTRIBUTE . '.*' => 'required|string|min:1',
		];
	}

	/**
	 * {@inheritDoc}
	 */
	protected function processValidatedValues(array $values, array $files): void
	{
		$this->photoIDs = $values[HasPhotoIDs::PHOTO_IDS_ATTRIBUTE];
		$this->tags = $values[HasTags::TAGS_ATTRIBUTE];
	}
}