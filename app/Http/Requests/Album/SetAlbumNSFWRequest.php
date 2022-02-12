<?php

namespace App\Http\Requests\Album;

use App\Http\Requests\BaseApiRequest;
use App\Http\Requests\Contracts\HasAbstractAlbum;
use App\Http\Requests\Contracts\HasBaseAlbum;
use App\Http\Requests\Traits\HasBaseAlbumTrait;
use App\Rules\RandomIDRule;

/**
 * Class SetAlbumNSFWRequest.
 *
 * This class is either a misnomer and should rather be called
 * `ToggleAlbumNSFWRequest` or receive an explicit boolean which indicates the
 * desired NSFW state.
 *
 * TODO: Fix the class, see above.
 */
class SetAlbumNSFWRequest extends BaseApiRequest implements HasBaseAlbum
{
	use HasBaseAlbumTrait;

	/**
	 * {@inheritDoc}
	 */
	public function authorize(): bool
	{
		return $this->authorizeAlbumWriteByModel($this->album);
	}

	/**
	 * {@inheritDoc}
	 */
	public function rules(): array
	{
		return [
			HasAbstractAlbum::ALBUM_ID_ATTRIBUTE => ['required', new RandomIDRule(false)],
		];
	}

	/**
	 * {@inheritDoc}
	 */
	protected function processValidatedValues(array $values, array $files): void
	{
		$this->album = $this->albumFactory->findModelOrFail(
			$values[HasAbstractAlbum::ALBUM_ID_ATTRIBUTE]
		);
	}
}
