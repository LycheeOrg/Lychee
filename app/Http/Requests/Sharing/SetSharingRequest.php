<?php

namespace App\Http\Requests\Sharing;

use App\Facades\AccessControl;
use App\Http\Requests\BaseApiRequest;
use App\Http\Requests\Contracts\HasAlbumIDs;
use App\Http\Requests\Contracts\HasUserIDs;
use App\Http\Requests\Traits\HasAlbumIDsTrait;
use App\Http\Requests\Traits\HasUserIDsTrait;
use App\Rules\IntegerIDRule;
use App\Rules\RandomIDRule;

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
			HasAlbumIDs::ALBUM_IDS_ATTRIBUTE => 'required|array|min:1',
			HasAlbumIDs::ALBUM_IDS_ATTRIBUTE . '.*' => ['required', new RandomIDRule(false)],
			HasUserIDs::USER_IDS_ATTRIBUTE => 'required|array|min:1',
			HasUserIDs::USER_IDS_ATTRIBUTE . '.*' => ['required', new IntegerIDRule(false)],
		];
	}

	/**
	 * {@inheritDoc}
	 */
	protected function processValidatedValues(array $values, array $files): void
	{
		$this->albumIDs = $values[HasAlbumIDs::ALBUM_IDS_ATTRIBUTE];
		$this->userIDs = $values[HasUserIDs::USER_IDS_ATTRIBUTE];
	}
}
