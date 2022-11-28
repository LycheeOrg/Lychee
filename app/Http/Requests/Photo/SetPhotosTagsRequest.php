<?php

namespace App\Http\Requests\Photo;

use App\Http\Requests\BaseApiRequest;
use App\Http\Requests\Contracts\HasPhotos;
use App\Http\Requests\Contracts\HasTags;
use App\Http\Requests\Traits\Authorize\AuthorizeCanEditPhotosTrait;
use App\Http\Requests\Traits\HasPhotosTrait;
use App\Http\Requests\Traits\HasTagsTrait;
use App\Models\Photo;
use App\Rules\RandomIDRule;

class SetPhotosTagsRequest extends BaseApiRequest implements HasPhotos, HasTags
{
	use HasPhotosTrait;
	use HasTagsTrait;
	use AuthorizeCanEditPhotosTrait;

	public const SHALL_OVERRIDE_ATTRIBUTE = 'shall_override';

	public bool $shallOverride;

	/**
	 * {@inheritDoc}
	 */
	public function rules(): array
	{
		return [
			self::SHALL_OVERRIDE_ATTRIBUTE => 'required|boolean',
			HasPhotos::PHOTO_IDS_ATTRIBUTE => 'required|array|min:1',
			HasPhotos::PHOTO_IDS_ATTRIBUTE . '.*' => ['required', new RandomIDRule(false)],
			HasTags::TAGS_ATTRIBUTE => 'present|array',
			HasTags::TAGS_ATTRIBUTE . '.*' => 'required|string|min:1',
		];
	}

	/**
	 * {@inheritDoc}
	 */
	protected function processValidatedValues(array $values, array $files): void
	{
		$this->photos = Photo::query()->without(['size_variants'])->findOrFail($values[HasPhotos::PHOTO_IDS_ATTRIBUTE]);
		$this->tags = $values[HasTags::TAGS_ATTRIBUTE];
		$this->shallOverride = $values[self::SHALL_OVERRIDE_ATTRIBUTE];
	}
}