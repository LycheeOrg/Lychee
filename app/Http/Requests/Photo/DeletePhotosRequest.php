<?php

namespace App\Http\Requests\Photo;

use App\Http\Requests\BaseApiRequest;
use App\Http\Requests\Contracts\HasPhotos;
use App\Http\Requests\Traits\HasPhotosTrait;
use App\Models\Photo;
use App\Rules\RandomIDRule;

class DeletePhotosRequest extends BaseApiRequest implements HasPhotos
{
	use HasPhotosTrait;

	/**
	 * {@inheritDoc}
	 */
	public function authorize(): bool
	{
		return $this->authorizePhotoWriteByModels($this->photos);
	}

	/**
	 * {@inheritDoc}
	 */
	public function rules(): array
	{
		return [
			HasPhotos::PHOTO_IDS_ATTRIBUTE => 'required|array|min:1',
			HasPhotos::PHOTO_IDS_ATTRIBUTE . '.*' => ['required', new RandomIDRule(false)],
		];
	}

	/**
	 * {@inheritDoc}
	 */
	protected function processValidatedValues(array $values, array $files): void
	{
		// Size variants and symlinks are required to properly delete the
		// media files from disk.
		// Load them eagerly for all photos at once to avoid iterated DB
		// requests for each photo later.
		$this->photos = Photo::query()
			->with(['size_variants', 'size_variants.sym_links'])
			->whereIn('id', $values[HasPhotos::PHOTO_IDS_ATTRIBUTE])
			->get();
	}
}
