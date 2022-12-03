<?php

namespace App\Http\Requests\Photo;

use App\Http\Requests\BaseApiRequest;
use App\Http\Requests\Contracts\HasPhotoIDs;
use App\Http\Requests\Contracts\RequestAttribute;
use App\Http\Requests\Traits\HasPhotoIDsTrait;
use App\Models\Photo;
use App\Policies\PhotoPolicy;
use App\Rules\RandomIDRule;
use Illuminate\Support\Facades\Gate;

class DeletePhotosRequest extends BaseApiRequest implements HasPhotoIDs
{
	use HasPhotoIDsTrait;

	/**
	 * {@inheritDoc}
	 */
	public function authorize(): bool
	{
		return Gate::check(PhotoPolicy::CAN_EDIT_ID, [Photo::class, $this->photoIDs()]);
	}

	/**
	 * {@inheritDoc}
	 */
	public function rules(): array
	{
		return [
			RequestAttribute::PHOTO_IDS_ATTRIBUTE => 'required|array|min:1',
			RequestAttribute::PHOTO_IDS_ATTRIBUTE . '.*' => ['required', new RandomIDRule(false)],
		];
	}

	/**
	 * {@inheritDoc}
	 */
	protected function processValidatedValues(array $values, array $files): void
	{
		// As we are going to delete the photos anyway, we don't load the
		// models for efficiency reasons.
		// Instead, we use mass deletion via low-level SQL queries later.
		$this->photoIDs = $values[RequestAttribute::PHOTO_IDS_ATTRIBUTE];
	}
}
