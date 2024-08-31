<?php

namespace App\Http\Requests\Traits\Authorize;

use App\Models\Photo;
use App\Policies\PhotoPolicy;
use Illuminate\Support\Facades\Gate;

/**
 * Determines if the user is authorized to modify or write into the
 * designated photo.
 */
trait AuthorizeCanEditPhotoTrait
{
	/**
	 * {@inheritDoc}
	 */
	public function authorize(): bool
	{
		return Gate::check(PhotoPolicy::CAN_EDIT, [Photo::class, $this->photo]);
	}
}