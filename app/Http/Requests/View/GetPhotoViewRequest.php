<?php

namespace App\Http\Requests\View;

use App\Contracts\Http\Requests\HasPhoto;
use App\Http\Requests\BaseApiRequest;
use App\Http\Requests\Traits\HasPhotoTrait;
use App\Models\Photo;
use App\Policies\PhotoPolicy;
use App\Rules\RandomIDRule;
use Illuminate\Support\Facades\Gate;

class GetPhotoViewRequest extends BaseApiRequest implements HasPhoto
{
	use HasPhotoTrait;
	public const URL_QUERY_PARAM = 'p';

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
		return [
			self::URL_QUERY_PARAM => ['required', new RandomIDRule(false)],
		];
	}

	/**
	 * {@inheritDoc}
	 */
	protected function processValidatedValues(array $values, array $files): void
	{
		$this->photo = Photo::query()
			->with(['album', 'size_variants', 'size_variants.sym_links'])
			->firstOrFail($values[self::URL_QUERY_PARAM]);
	}
}
