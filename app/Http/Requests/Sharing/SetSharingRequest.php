<?php

namespace App\Http\Requests\Sharing;

use App\Facades\AccessControl;
use App\Http\Requests\BaseApiRequest;
use App\Http\Requests\Contracts\HasAlbumIDs;
use App\Http\Requests\Contracts\HasAlbumModelIDs;
use App\Http\Requests\Contracts\HasUserIDs;
use App\Http\Requests\Traits\HasAlbumIDsTrait;
use App\Http\Requests\Traits\HasUserIDsTrait;
use App\Rules\ModelIDListRule;

class SetSharingRequest extends BaseApiRequest implements HasAlbumModelIDs, HasUserIDs
{
	use HasAlbumIDsTrait;
	use HasUserIDsTrait;

	/**
	 * {@inheritDoc}
	 */
	public function authorize(): bool
	{
		// This should always return true, because we already check that the
		// request is made by an admin during authentication (see
		// `routes/web.php`).
		// But better safe than sorry.
		return AccessControl::is_admin();
	}

	/**
	 * {@inheritDoc}
	 */
	public function rules(): array
	{
		return [
			HasAlbumIDs::ALBUM_IDS_ATTRIBUTE => ['required', new ModelIDListRule()],
			HasUserIDs::USER_IDS_ATTRIBUTE => ['required', new ModelIDListRule()],
		];
	}

	/**
	 * {@inheritDoc}
	 */
	protected function processValidatedValues(array $values, array $files): void
	{
		$this->albumIDs = explode(',', $values[HasAlbumIDs::ALBUM_IDS_ATTRIBUTE]);
		array_walk($this->albumIDs, function (&$id) { $id = intval($id); });
		$this->userIDs = explode(',', $values[HasUserIDs::USER_IDS_ATTRIBUTE]);
		array_walk($this->userIDs, function (&$id) { $id = intval($id); });
	}
}
