<?php

namespace App\Http\Requests\Album;

use App\Contracts\AbstractAlbum;
use App\Exceptions\Internal\InvalidSmartIdException;
use App\Factories\AlbumFactory;
use App\Http\Requests\BaseApiRequest;
use App\Http\Requests\Contracts\HasAlbums;
use App\Http\Requests\Traits\HasAlbumsTrait;
use App\Rules\RandomIDListRule;
use Illuminate\Contracts\Container\BindingResolutionException;

class ArchiveAlbumsRequest extends BaseApiRequest implements HasAlbums
{
	use HasAlbumsTrait;

	/**
	 * {@inheritDoc}
	 */
	public function authorize(): bool
	{
		/** @var AbstractAlbum $album */
		foreach ($this->albums as $album) {
			if (!$this->authorizeAlbumAccessByModel($album)) {
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
			HasAlbums::ALBUM_IDS_ATTRIBUTE => ['required', new RandomIDListRule()],
		];
	}

	/**
	 * {@inheritDoc}
	 *
	 * @throws InvalidSmartIdException
	 * @throws BindingResolutionException
	 */
	protected function processValidatedValues(array $values, array $files): void
	{
		/** @var AlbumFactory $albumFactory */
		$albumFactory = resolve(AlbumFactory::class);
		// TODO: `App\Actions\Album\Archive::compressAlbum` iterates over the original size variant of each photo in the album; we should eagerly load them for higher efficiency.
		$this->albums = $albumFactory->findWhereIDsIn(
			explode(',', $values[HasAlbums::ALBUM_IDS_ATTRIBUTE])
		);
	}
}
