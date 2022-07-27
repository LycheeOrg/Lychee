<?php

namespace App\Http\Requests\Album;

use App\Exceptions\PasswordRequiredException;
use App\Http\Requests\BaseApiRequest;
use App\Http\Requests\Contracts\HasAbstractAlbum;
use App\Http\Requests\Traits\HasAbstractAlbumTrait;
use App\Models\Extensions\BaseAlbum;
use App\Rules\AlbumIDRule;

class GetAlbumRequest extends BaseApiRequest implements HasAbstractAlbum
{
	use HasAbstractAlbumTrait;

	/**
	 * {@inheritDoc}
	 */
	public function authorize(): bool
	{
		$result = $this->authorizeAlbumAccess($this->album);

		// In case of a password protected album, we must throw an exception
		// with a special error message ("Password required") such that the
		// front-end shows the password dialog if a password is set, but
		// does not show the dialog otherwise.
		if (
			!$result &&
			$this->album instanceof BaseAlbum &&
			$this->album->is_public &&
			$this->album->password !== null
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
			HasAbstractAlbum::ALBUM_ID_ATTRIBUTE => ['required', new AlbumIDRule(false)],
		];
	}

	/**
	 * {@inheritDoc}
	 */
	protected function processValidatedValues(array $values, array $files): void
	{
		$this->album = $this->albumFactory->findAbstractAlbumOrFail($values[HasAbstractAlbum::ALBUM_ID_ATTRIBUTE]);
	}
}
