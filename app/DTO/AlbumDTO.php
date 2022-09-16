<?php

namespace App\DTO;

use App\Contracts\AbstractAlbum;
use App\Models\Album;

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
		// Base convertion to array
		$albumDTO = $this->album->toArray();

		// if albums has sub-albums provided also apply conversion on them
		if (key_exists('albums', $albumDTO) && $this->album instanceof Album) {
			$albumDTO['albums'] = $this->album->children->map(fn (Album $a) => ((new AlbumDTO($a))->toArray()));
		}

		// add the rights
		$albumDTO['rights'] = AlbumRights::ofAlbum($this->album);

		// Provide the policies if the user can edit.
		if ($albumDTO['rights']->can_edit) {
			$albumDTO['policies'] = AlbumProtectionPolicy::ofAlbum($this->album);
		}

		return $albumDTO;
	}
}