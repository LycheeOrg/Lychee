<?php

namespace App\Legacy\V1\Requests\Photo;

use App\Http\Requests\AbstractEmptyRequest;
use Illuminate\Support\Facades\Auth;

class ClearSymLinkRequest extends AbstractEmptyRequest
{
	/**
	 * {@inheritDoc}
	 */
	public function authorize(): bool
	{
		return Auth::user()?->may_administrate === true;
	}
}
