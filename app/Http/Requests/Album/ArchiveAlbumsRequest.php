<?php

namespace App\Http\Requests\Album;

use App\Contracts\AbstractAlbum;
use App\Http\Requests\BaseApiRequest;
use App\Http\Requests\Contracts\HasAlbums;
use App\Http\Requests\Contracts\RequestAttribute;
use App\Http\Requests\Traits\HasAlbumsTrait;
use App\Policies\AlbumPolicy;
use App\Rules\AlbumIDListRule;
use Illuminate\Support\Facades\Gate;

/**
 * @implements HasAlbums<\App\Contracts\AbstractAlbum>
 */
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
			if (!Gate::check(AlbumPolicy::CAN_ACCESS, $album)) {
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
			RequestAttribute::ALBUM_IDS_ATTRIBUTE => ['required', new AlbumIDListRule()],
		];
	}

	/**
	 * {@inheritDoc}
	 */
	protected function processValidatedValues(array $values, array $files): void
	{
		// TODO: `App\Actions\Album\Archive::compressAlbum` iterates over the original size variant of each photo in the album; we should eagerly load them for higher efficiency.
		$this->albums = $this->albumFactory->findAbstractAlbumsOrFail(
			explode(',', $values[RequestAttribute::ALBUM_IDS_ATTRIBUTE])
		);
	}
}
