<?php

declare(strict_types=1);

namespace App\Http\Requests\Photo;

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
