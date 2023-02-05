<?php

namespace App\Http\Requests\Album;

use App\Contracts\Http\Requests\HasAlbumIDs;
use App\Contracts\Http\Requests\RequestAttribute;
use App\Http\Requests\BaseApiRequest;
use App\Http\Requests\Traits\HasAlbumIDsTrait;
use App\Http\RuleSets\Album\DeleteAlbumsRuleSet;
use App\Models\Album;
use App\Policies\AlbumPolicy;
use Illuminate\Support\Facades\Gate;

class DeleteAlbumsRequest extends BaseApiRequest implements HasAlbumIDs
{
	use HasAlbumIDsTrait;

	/**
	 * {@inheritDoc}
	 */
	public function authorize(): bool
	{
		return Gate::check(AlbumPolicy::CAN_EDIT_ID, [Album::class, $this->albumIDs()]);
	}

	/**
	 * {@inheritDoc}
	 */
	public function rules(): array
	{
		return DeleteAlbumsRuleSet::rules();
	}

	/**
	 * {@inheritDoc}
	 */
	protected function processValidatedValues(array $values, array $files): void
	{
		// As we are going to delete the albums anyway, we don't load the
		// models for efficiency reasons.
		// Instead, we use mass deletion via low-level SQL queries later.
		$this->albumIDs = $values[RequestAttribute::ALBUM_IDS_ATTRIBUTE];
	}
}
