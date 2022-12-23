<?php

namespace App\Http\Requests\Import;

use App\Contracts\Models\AbstractAlbum;
use App\Http\Requests\AbstractEmptyRequest;
use App\Policies\AlbumPolicy;
use Illuminate\Support\Facades\Gate;

class CancelImportServerRequest extends AbstractEmptyRequest
{
	/**
	 * {@inheritDoc}
	 */
	public function authorize(): bool
	{
		return Gate::check(AlbumPolicy::CAN_IMPORT_FROM_SERVER, AbstractAlbum::class);
	}
}
