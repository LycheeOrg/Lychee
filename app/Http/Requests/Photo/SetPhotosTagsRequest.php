<?php

namespace App\Http\Requests\Photo;

use App\Http\Requests\BaseApiRequest;
use App\Http\Requests\Contracts\HasPhotoIDs;
use App\Http\Requests\Contracts\HasTags;
use App\Http\Requests\Traits\HasPhotoIDsTrait;
use App\Http\Requests\Traits\HasTagsTrait;
use App\Rules\ModelIDListRule;
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
			HasPhotoIDs::PHOTO_IDS_ATTRIBUTE => ['required', new ModelIDListRule()],
			HasTags::TAGS_ATTRIBUTE => ['present', new TagsRule(true)],
		];
	}

	/**
	 * {@inheritDoc}
	 */
	protected function processValidatedValues(array $values, array $files): void
	{
		$this->photoIDs = explode(',', $values[HasPhotoIDs::PHOTO_IDS_ATTRIBUTE]);
		array_walk($this->photoIDs, function (&$id) { $id = intval($id); });
		$this->tags = $values[HasTags::TAGS_ATTRIBUTE];
	}
}