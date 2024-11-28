<?php

namespace App\Http\Requests\Album;

use App\Contracts\Http\Requests\HasAlbums;
use App\Contracts\Http\Requests\RequestAttribute;
use App\Contracts\Models\AbstractAlbum;
use App\Exceptions\PasswordRequiredException;
use App\Http\Requests\BaseApiRequest;
use App\Http\Requests\Traits\HasAlbumsTrait;
use App\Models\Album;
use App\Models\Extensions\BaseAlbum;
use App\Policies\AlbumPolicy;
use App\Rules\AlbumIDRule;
use Illuminate\Support\Facades\Gate;

/**
 * @implements HasAlbums<Album>
 */
class TargetListAlbumRequest extends BaseApiRequest implements HasAlbums
{
	/** @phpstan-use HasAlbumsTrait<Album> */
	use HasAlbumsTrait;

	/**
	 * {@inheritDoc}
	 */
	public function authorize(): bool
	{
		$result = Gate::check(AlbumPolicy::CAN_ACCESS, [AbstractAlbum::class, $this->album]);

		// In case of a password protected album, we must throw an exception
		// with a special error message ("Password required") such that the
		// front-end shows the password dialog if a password is set, but
		// does not show the dialog otherwise.
		if (
			!$result &&
			$this->album instanceof BaseAlbum &&
			$this->album->public_permissions()?->password !== null
		) {
			throw new PasswordRequiredException();
		}

		return $result;
	}

	/**
	 * {@inheritDoc}
	 */
	public function rules(): array
	{
		return [
			RequestAttribute::ALBUM_IDS_ATTRIBUTE => 'sometimes|array|min:1',
			RequestAttribute::ALBUM_IDS_ATTRIBUTE . '.*' => ['required', new AlbumIDRule(false)],
		];
	}

	/**
	 * {@inheritDoc}
	 */
	protected function processValidatedValues(array $values, array $files): void
	{
		$album_ids = $values[RequestAttribute::ALBUM_IDS_ATTRIBUTE] ?? [];
		/** @phpstan-ignore-next-line */
		$this->albums = $this->albumFactory->findAbstractAlbumsOrFail($album_ids);
	}
}
