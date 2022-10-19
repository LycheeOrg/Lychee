<?php

namespace App\DTO;

use Illuminate\Support\Collection;

class PositionData extends ArrayableDTO
{
	/**
	 * @param string|null $id        the ID of the album; `null` for root album
	 * @param string|null $title     the title of the album; `null` if untitled
	 * @param Collection  $photos    the collection of photos with position data to be shown on map
	 * @param string|null $track_url the URL of the album's track
	 */
	public function __construct(
		public ?string $id,
		public ?string $title,
		public Collection $photos,
		public ?string $track_url)
	{
	}
}