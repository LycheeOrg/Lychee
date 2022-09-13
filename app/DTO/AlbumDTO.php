<?php

namespace App\DTO;

use App\Contracts\AbstractAlbum;
use App\Models\Album;
use App\Policies\AlbumPolicy;
use Illuminate\Support\Facades\Gate;

/**
 * Data Transfer Object (DTO) for Albums.
 *
 * This allows us to decorate the Album with its associated current user rights.
 */
class AlbumDTO extends DTO
{
	public function __construct(
		private AbstractAlbum $album
	) {
	}

	/**
	 * Album with it's associated righs.
	 *
	 * @return array
	 */
	public function toArray(): array
	{
		$albumDTO = $this->album->toArray();
		if (key_exists('albums', $albumDTO) && $this->album instanceof Album) {
			$albumDTO['albums'] = $this->album->children->map(fn (Album $a) => ((new AlbumDTO($a))->toArray()));
		}
		$albumDTO['rights'] = AlbumRights::ofAlbum($this->album);

		if (Gate::check(AlbumPolicy::CAN_EDIT, [AbstractAlbum::class, $this->album])) {
			$albumDTO['policies'] = AlbumProtectionPolicy::ofAlbum($this->album);
		}

		return $albumDTO;
	}
}