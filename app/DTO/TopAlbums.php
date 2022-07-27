<?php

namespace App\DTO;

use Illuminate\Support\Collection;

/**
 * Data Transfer Object (DTO) to transmit the top albums to the client.
 *
 * This DTO differentiates between albums which are owned by the user and
 * "shared" albums which the user does not own, but is allowed to see.
 * The term "shared album" might be a little misleading here.
 * Albums which are owned by the user himself may also be shared (with
 * other users.)
 * Actually, in this context "shared albums" means "foreign albums".
 */
class TopAlbums extends DTO
{
	public Collection $smartAlbums;
	public Collection $tagAlbums;
	public Collection $albums;
	public Collection $sharedAlbums;

	public function __construct(
		Collection $smartAlbums,
		Collection $tagAlbums,
		Collection $albums,
		?Collection $sharedAlbums = null
	) {
		$this->smartAlbums = $smartAlbums;
		$this->tagAlbums = $tagAlbums;
		$this->albums = $albums;
		$this->sharedAlbums = $sharedAlbums ?? new Collection();
	}

	/**
	 * {@inheritDoc}
	 */
	public function toArray(): array
	{
		return [
			'smart_albums' => $this->smartAlbums->toArray(),
			'tag_albums' => $this->tagAlbums->toArray(),
			'albums' => $this->albums->toArray(),
			'shared_albums' => $this->sharedAlbums->toArray(),
		];
	}
}
