<?php

namespace App\DTO;

use Illuminate\Support\Collection;

class PositionData extends DTO
{
	public ?string $id;
	public ?string $title;
	public Collection $photos;
	public ?string $track_url;

	/**
	 * Constructor.
	 *
	 * @param string|null $id        the ID of the album; `null` for root album
	 * @param string|null $title     the title of the album; `null` if untitled
	 * @param Collection  $photos    the collection of photos with position data to be shown on map
	 * @param string|null $track_url the URL of the album's track
	 */
	public function __construct(?string $id, ?string $title, Collection $photos, ?string $track_url)
	{
		$this->id = $id;
		$this->title = $title;
		$this->photos = $photos;
		$this->track_url = $track_url;
	}

	/**
	 * {@inheritDoc}
	 */
	public function toArray(): array
	{
		return [
			'id' => $this->id,
			'title' => $this->title,
			'photos' => $this->photos->toArray(),
			'track_url' => $this->track_url,
		];
	}
}