<?php

namespace App\Http\Requests\Sharing;

use App\Facades\AccessControl;
use App\Http\Requests\BaseApiRequest;
use App\Http\Requests\Contracts\HasAlbumIDs;
use App\Http\Requests\Contracts\HasUserIDs;
use App\Http\Requests\Traits\HasAlbumIDsTrait;
use App\Http\Requests\Traits\HasUserIDsTrait;
use App\Rules\IntegerIDListRule;
use App\Rules\RandomIDListRule;

class SetSharingRequest extends BaseApiRequest implements HasAlbumIDs, HasUserIDs
{
	use HasAlbumIDsTrait;
	use HasUserIDsTrait;

	/**
	 * {@inheritDoc}
	 */
	public function authorize(): bool
	{
		return AccessControl::can_upload();
	}

	/**
	 * {@inheritDoc}
	 */
	public function rules(): array
	{
		return [
			HasAlbumIDs::ALBUM_IDS_ATTRIBUTE => ['required', new RandomIDListRule()],
			HasUserIDs::USER_IDS_ATTRIBUTE => ['required', new IntegerIDListRule()],
		];
	}

	/**
	 * {@inheritDoc}
	 */
	protected function processValidatedValues(array $values, array $files): void
	{
		$this->albumIDs = explode(',', $values[HasAlbumIDs::ALBUM_IDS_ATTRIBUTE]);
		$this->userIDs = explode(',', $values[HasUserIDs::USER_IDS_ATTRIBUTE]);
		array_walk($this->userIDs, function (&$id) { $id = intval($id); });
	}
}
