<?php

declare(strict_types=1);

namespace App\Http\Requests\Album;

use App\Contracts\Http\Requests\HasAlbums;
use App\Contracts\Http\Requests\RequestAttribute;
use App\Contracts\Models\AbstractAlbum;
use App\Http\Requests\BaseApiRequest;
use App\Http\Requests\Traits\HasAlbumsTrait;
use App\Http\RuleSets\Album\ArchiveAlbumRuleSet;
use App\Policies\AlbumPolicy;
use Illuminate\Support\Facades\Gate;

/**
 * @implements HasAlbums<\App\Contracts\Models\AbstractAlbum>
 */
class ArchiveAlbumsRequest extends BaseApiRequest implements HasAlbums
{
	/** @use HasAlbumsTrait<AbstractAlbum> */
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
		return ArchiveAlbumRuleSet::rules();
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
