<?php

namespace App\Http\Requests\Sharing;

use App\Contracts\AbstractAlbum;
use App\Http\Requests\BaseApiRequest;
use App\Http\Requests\Contracts\HasAlbumIDs;
use App\Http\Requests\Contracts\HasUserIDs;
use App\Http\Requests\Traits\HasAlbumIDsTrait;
use App\Http\Requests\Traits\HasUserIDsTrait;
use App\Policies\AlbumPolicy;
use App\Rules\IntegerIDRule;
use App\Rules\RandomIDRule;
use Illuminate\Support\Facades\Gate;

class SetSharingRequest extends BaseApiRequest implements HasAlbumIDs, HasUserIDs
{
	use HasAlbumIDsTrait;
	use HasUserIDsTrait;

	/**
	 * {@inheritDoc}
	 */
	public function authorize(): bool
	{
		return Gate::check(AlbumPolicy::CAN_SHARE_ID, [AbstractAlbum::class, $this->albumIDs]);
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
