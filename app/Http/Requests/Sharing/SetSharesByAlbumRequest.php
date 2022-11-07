<?php

namespace App\Http\Requests\Sharing;

use App\Contracts\AbstractAlbum;
use App\Http\Requests\BaseApiRequest;
use App\Http\Requests\Contracts\HasAbstractAlbum;
use App\Http\Requests\Contracts\HasBaseAlbum;
use App\Http\Requests\Contracts\HasUserIDs;
use App\Http\Requests\Traits\HasBaseAlbumTrait;
use App\Http\Requests\Traits\HasUserIDsTrait;
use App\Policies\AlbumPolicy;
use App\Rules\IntegerIDRule;
use App\Rules\RandomIDRule;
use Illuminate\Support\Facades\Gate;

/**
 * Represents a request for setting the shares of a specific album.
 *
 * Only the owner (or the admin) of the album can set the shares.
 */
class SetSharesByAlbumRequest extends BaseApiRequest implements HasBaseAlbum, HasUserIDs
{
	use HasBaseAlbumTrait;
	use HasUserIDsTrait;

	/**
	 * {@inheritDoc}
	 */
	public function authorize(): bool
	{
		return Gate::check(AlbumPolicy::CAN_SHARE_WITH_USERS, [AbstractAlbum::class, $this->album]);
	}

	/**
	 * {@inheritDoc}
	 */
	public function rules(): array
	{
		return [
			HasAbstractAlbum::ALBUM_ID_ATTRIBUTE => ['required', new RandomIDRule(false)],
			HasUserIDs::USER_IDS_ATTRIBUTE => 'present|array',
			HasUserIDs::USER_IDS_ATTRIBUTE . '.*' => ['required', new IntegerIDRule(false)],
		];
	}

	/**
	 * {@inheritDoc}
	 */
	protected function processValidatedValues(array $values, array $files): void
	{
		$this->album = $this->albumFactory->findBaseAlbumOrFail($values[HasAbstractAlbum::ALBUM_ID_ATTRIBUTE]);
		$this->userIDs = $values[HasUserIDs::USER_IDS_ATTRIBUTE];
	}
}