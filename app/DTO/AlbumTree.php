<?php

namespace App\DTO;

use App\Models\Album;
use Illuminate\Support\Collection;

/**
 * Data Transfer Object (DTO) to transmit the album tree to the client.
 *
 * This DTO differentiates between albums which are owned by the user and
 * "shared" albums which the user does not own, but is allowed to see.
 * The term "shared album" might be a little misleading here.
 * Albums which are owned by the user himself may also be shared (with
 * other users.)
 * Actually, in this context "shared albums" means "foreign albums".
 */
class AlbumTree extends AbstractDTO
{
	public Collection $albums;
	public Collection $sharedAlbums;

	public function __construct(
		Collection $albums,
		?Collection $sharedAlbums = null
	) {
		$this->albums = $albums;
		$this->sharedAlbums = $sharedAlbums ?? new Collection();
	}

	/**
	 * {@inheritDoc}
	 */
	public function toArray(): array
	{
		return [
			'albums' => self::reduceTree($this->albums),
			'shared_albums' => self::reduceTree($this->sharedAlbums),
		];
	}

	/**
	 * Recursively converts the album tree into array and reduces the
	 * attributes to those needed by the front-end.
	 *
	 * @param Collection $albums
	 *
	 * @return array
	 */
	private static function reduceTree(Collection $albums): array
	{
		return $albums->map(fn (Album $album) => [
			'id' => $album->id,
			'title' => $album->title,
			'thumb' => $album->thumb?->toArray(),
			'parent_id' => $album->parent_id,
			'albums' => self::reduceTree($album->children),
		])->all();
	}
}
