<?php

namespace App\Livewire\DTO;

use App\Contracts\Models\AbstractAlbum;
use App\Enum\LicenseType;
use App\Models\Album;
use App\Models\Configs;
use App\Models\Extensions\BaseAlbum;

class AlbumFormatted
{
	public ?string $url;
	public string $title;
	public ?string $min_taken_at = null;
	public ?string $max_taken_at = null;
	public string $album_id;
	public string $license = '';
	public int $num_children = 0;
	public int $num_photos = 0;
	public bool $can_download;
	public ?string $created_at = null;
	public ?string $description = null;

	public function __construct(AbstractAlbum $album, ?string $url)
	{
		$min_max_date_format = Configs::getValueAsString('date_format_hero_min_max');
		$create_date_format = Configs::getValueAsString('date_format_hero_created_at');
		$this->url = $url;
		$this->title = $album->title;
		if ($album instanceof BaseAlbum) {
			$this->min_taken_at = $album->min_taken_at?->format($min_max_date_format);
			$this->max_taken_at = $album->max_taken_at?->format($min_max_date_format);
			$this->created_at = $album->created_at->format($create_date_format);
			$this->description = $album->description;
		}
		if ($album instanceof Album) {
			$this->num_children = $album->num_children;
			$this->num_photos = $album->num_photos;
			$this->license = $album->license === LicenseType::NONE ? '' : $album->license->localization();
		}
	}
}