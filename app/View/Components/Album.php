<?php

namespace App\View\Components;

use App\Contracts\Models\AbstractAlbum;
use App\DTO\AlbumProtectionPolicy;
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

	public bool $is_tag_album;
	public bool $has_cover_id;
	public bool $has_subalbum;

	public function __construct(AbstractAlbum $data)
	{
		$this->id = $data->id;
		$this->thumb = $data->thumb;
		$this->title = $data->title;

		if ($data instanceof BaseSmartAlbum) {
			$policy = AlbumProtectionPolicy::ofSmartAlbum($data);
		} else {
			/** @var BaseAlbum $data */
			$policy = AlbumProtectionPolicy::ofBaseAlbum($data);
		}

		$this->is_nsfw = $policy->is_nsfw;
		$this->is_nsfw_blurred = $this->is_nsfw && Configs::getValueAsBool('nsfw_blur');
		$this->is_public = $policy->is_public;
		$this->is_link_required = $policy->is_link_required;
		$this->is_password_required = $policy->is_password_required;

		$this->is_tag_album = $data instanceof TagAlbum;
		$this->has_cover_id = $data instanceof AlbumModel && $data->cover_id !== null && $data->cover_id === $data->thumb->id;
		$this->has_subalbum = $data instanceof AlbumModel && !$data->isLeaf();
	}

	public function render()
	{
		return view('components.gallery.album');
	}
}