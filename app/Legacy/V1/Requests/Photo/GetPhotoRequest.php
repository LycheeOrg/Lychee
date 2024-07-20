<?php

namespace App\Legacy\V1\Requests\Photo;

use App\Http\Requests\BaseApiRequest;
use App\Http\RuleSets\Photo\GetPhotoRuleSet;
use App\Legacy\V1\Contracts\Http\Requests\HasPhoto;
use App\Legacy\V1\Contracts\Http\Requests\RequestAttribute;
use App\Legacy\V1\Requests\Traits\HasPhotoTrait;
use App\Models\Photo;
use App\Policies\PhotoPolicy;
use Illuminate\Support\Facades\Gate;

class GetPhotoRequest extends BaseApiRequest implements HasPhoto
{
	use HasPhotoTrait;

	/**
	 * {@inheritDoc}
	 */
	public function authorize(): bool
	{
		return Gate::check(PhotoPolicy::CAN_SEE, $this->photo);
	}

	/**
	 * {@inheritDoc}
	 */
	public function rules(): array
	{
		return GetPhotoRuleSet::rules();
	}

	/**
	 * {@inheritDoc}
	 */
	protected function processValidatedValues(array $values, array $files): void
	{
		/** @var ?string $photoID */
		$photoID = $values[RequestAttribute::PHOTO_ID_ATTRIBUTE];
		$this->photo = Photo::query()
			->with(['size_variants', 'size_variants.sym_links'])
			->findOrFail($photoID);
	}
}
