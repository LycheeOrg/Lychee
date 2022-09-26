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
class TopAlbums extends ArrayableDTO
{
	public function __construct(
		public Collection $smart_albums,
		public Collection $tag_albums,
		public Collection $albums,
		public ?Collection $shared_albums = null
	) {
		$this->shared_albums ??= new Collection();
	}
}
