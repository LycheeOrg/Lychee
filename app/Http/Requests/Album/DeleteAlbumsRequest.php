<?php

namespace App\Http\Requests\Album;

use App\Contracts\AbstractAlbum;
use App\Exceptions\Internal\InvalidSmartIdException;
use App\Factories\AlbumFactory;
use App\Http\Requests\BaseApiRequest;
use App\Http\Requests\Contracts\HasAlbums;
use App\Http\Requests\Traits\HasAlbumsTrait;
use App\Rules\AlbumIDRule;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class DeleteAlbumsRequest extends BaseApiRequest implements HasAlbums
{
	use HasAlbumsTrait;

	/**
	 * {@inheritDoc}
	 */
	public function authorize(): bool
	{
		/** @var AbstractAlbum $album */
		foreach ($this->albums as $album) {
			if (!$this->authorizeAlbumWriteByModel($album)) {
				return false;
			}
		}

		return true;
	}

	/**
	 * {@inheritDoc}
	 */
	public function rules(): array
	{
		return [
			HasAlbums::ALBUM_IDS_ATTRIBUTE => 'required|array|min:1',
			HasAlbums::ALBUM_IDS_ATTRIBUTE . '.*' => ['required', new AlbumIDRule()],
		];
	}

	/**
	 * {@inheritDoc}
	 *
	 * @throws InvalidSmartIdException
	 * @throws BindingResolutionException
	 * @throws ModelNotFoundException
	 */
	protected function processValidatedValues(array $values, array $files): void
	{
		/** @var AlbumFactory $albumFactory */
		$albumFactory = resolve(AlbumFactory::class);
		$this->albums = $albumFactory->findWhereIDsIn(
			$values[HasAlbums::ALBUM_IDS_ATTRIBUTE]
		);
	}
}
