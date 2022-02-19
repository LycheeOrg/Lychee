<?php

namespace App\Http\Requests\View;

use App\Http\Requests\BaseApiRequest;
use App\Http\Requests\Contracts\HasPhoto;
use App\Http\Requests\Traits\HasPhotoTrait;
use App\Models\Photo;
use App\Rules\RandomIDRule;

class GetPhotoViewRequest extends BaseApiRequest implements HasPhoto
{
	use HasPhotoTrait;

	/**
	 * {@inheritDoc}
	 */
	public function authorize(): bool
	{
		return $this->authorizePhotoVisible($this->photo);
	}

	/**
	 * {@inheritDoc}
	 */
	public function rules(): array
	{
		return [
			'p' => ['required', new RandomIDRule(false)],
		];
	}

	/**
	 * {@inheritDoc}
	 */
	protected function processValidatedValues(array $values, array $files): void
	{
		$this->photo = Photo::query()
			->with(['album', 'size_variants', 'size_variants.sym_links'])
			->findOrFail($values[HasPhoto::PHOTO_ID_ATTRIBUTE]);
	}
}
