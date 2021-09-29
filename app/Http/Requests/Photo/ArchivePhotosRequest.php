<?php

namespace App\Http\Requests\Photo;

use App\Http\Requests\BaseApiRequest;
use App\Http\Requests\Contracts\HasPhotoIDs;
use App\Http\Requests\Contracts\HasSizeVariant;
use App\Http\Requests\Traits\HasPhotoIDsTrait;
use App\Http\Requests\Traits\HasSizeVariantTrait;
use App\Rules\ModelIDListRule;
use App\Rules\SizeVariantRule;

class ArchivePhotosRequest extends BaseApiRequest implements HasPhotoIDs, HasSizeVariant
{
	use HasPhotoIDsTrait;
	use HasSizeVariantTrait;

	/**
	 * {@inheritDoc}
	 */
	public function authorize(): bool
	{
		foreach ($this->photoIDs as $photoID) {
			if (!$this->authorizePhotoVisible($photoID)) {
				return false;
			}
		}

		return true;
	}

	/**
	 * {@inheritDoc}
	 */
	public function rules(): array
	{
		return [
			HasPhotoIDs::PHOTO_IDS_ATTRIBUTE => ['required', new ModelIDListRule()],
			HasSizeVariant::SIZE_VARIANT_ATTRIBUTE => ['required', new SizeVariantRule()],
		];
	}

	/**
	 * {@inheritDoc}
	 */
	protected function processValidatedValues(array $values, array $files): void
	{
		$this->photoIDs = explode(',', $values[HasPhotoIDs::PHOTO_IDS_ATTRIBUTE]);
		array_walk($this->photoIDs, function (&$id) { $id = intval($id); });
		$this->sizeVariant = $values[HasSizeVariant::SIZE_VARIANT_ATTRIBUTE];
	}
}
