<?php

namespace App\Http\Resources\Collections;

use App\Http\Resources\Models\PhotoResource;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Collection;

/**
 * Resource returned when querying for pictures on the map.
 */
class PositionDataResource extends JsonResource
{
	public ?string $id;
	public ?string $title;
	public ?string $track_url;

	/**
	 * @param string|null $id        the ID of the album; `null` for root album
	 * @param string|null $title     the title of the album; `null` if untitled
	 * @param Collection  $photos    the collection of photos with position data to be shown on map
	 * @param string|null $track_url the URL of the album's track
	 */
	public function __construct(
		?string $id,
		?string $title,
		Collection $photos,
		?string $track_url
	) {
		parent::__construct($photos);
		$this->id = $id;
		$this->title = $title;
		$this->track_url = $track_url;
	}

	/**
	 * Transform the resource into an array.
	 *
	 * @param \Illuminate\Http\Request $request
	 *
	 * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
	 */
	public function toArray($request)
	{
		return [
			'id' => $this->id,
			'title' => $this->title,
			'photos' => PhotoResource::collection($this->resource),
			'track_url' => $this->track_url,
		];
	}
}