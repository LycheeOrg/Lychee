<?php

declare(strict_types=1);

namespace App\Http\Requests\WebAuthn;

use App\Http\Requests\AbstractEmptyRequest;
use Illuminate\Support\Facades\Auth;

class ListCredentialsRequest extends AbstractEmptyRequest
{
	/**
	 * {@inheritDoc}
	 */
	public function authorize(): bool
	{
		return Auth::check();
	}
}
