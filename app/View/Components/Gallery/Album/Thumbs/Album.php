<?php

namespace App\View\Components\Gallery\Album\Thumbs;

use App\Contracts\Models\AbstractAlbum;
use App\DTO\AlbumProtectionPolicy;
use App\Enum\ThumbAlbumSubtitleType;
use App\Models\Album as AlbumModel;
use App\Models\Configs;
use App\Models\Extensions\BaseAlbum;
use App\Models\Extensions\Thumb;
use App\Models\TagAlbum;
use App\SmartAlbums\BaseSmartAlbum;
use Illuminate\View\Component;

class Album extends Component
{
	public string $id;
	public string $title;
	public ?Thumb $thumb;

	public bool $is_nsfw;
	public bool $is_nsfw_blurred;
	public bool $is_public;
	public bool $is_link_required;
	public bool $is_password_required;
	public string $subType;

	public bool $is_tag_album;
	public bool $is_cover_id;
	public bool $has_subalbum;

	public string $created_at = '';
	public ?string $min_taken_at = null;
	public ?string $max_taken_at = null;

	public function __construct(AbstractAlbum $data)
	{
		$date_format = Configs::getValueAsString('date_format_album_thumb');

		$this->subType = Configs::getValueAsEnum('album_subtitle_type', ThumbAlbumSubtitleType::class)->value;

		$this->id = $data->id;
		$this->thumb = $data->thumb;
		$this->title = $data->title;

		if ($data instanceof BaseSmartAlbum) {
			$policy = AlbumProtectionPolicy::ofSmartAlbum($data);
		} else {
			/** @var BaseAlbum $data */
			$this->max_taken_at = $data->max_taken_at?->format($date_format);
			$this->min_taken_at = $data->min_taken_at?->format($date_format);
			$this->created_at = $data->created_at->format($date_format);
			$policy = AlbumProtectionPolicy::ofBaseAlbum($data);
		}

		$this->is_nsfw = $policy->is_nsfw;
		$this->is_nsfw_blurred = $this->is_nsfw && Configs::getValueAsBool('nsfw_blur');
		$this->is_public = $policy->is_public;
		$this->is_link_required = $policy->is_link_required;
		$this->is_password_required = $policy->is_password_required;

		$this->is_tag_album = $data instanceof TagAlbum;
		// This aims to indicate whether the current thumb is used to determine the parent.
		$this->is_cover_id = $data instanceof AlbumModel && $data->thumb !== null && $data->parent?->cover_id === $data->thumb->id;
		$this->has_subalbum = $data instanceof AlbumModel && !$data->isLeaf();
	}

	public function render()
	{
		return view('components.gallery.album.thumbs.album');
	}
}