<?php

namespace App\View\Components;

use App\Contracts\AbstractAlbum;
use App\Models\Album as AlbumModel;
use App\Models\Configs;
use App\Models\Extensions\BaseAlbum;
use App\Models\Extensions\Thumb;
use App\Models\TagAlbum;
use Illuminate\View\Component;

class Album extends Component
{
	public string $id;
	public string $title;
	public bool $is_nsfw;
	public Thumb $thumb;
	public bool $is_public;
	public bool $has_password;
	public bool $is_tag_album;
	public bool $has_cover_id;
	public bool $has_subalbum;
	public bool $require_link;

	public function __construct(AbstractAlbum $data)
	{
		$this->id = $data->id;
		$this->is_nsfw = $data instanceof BaseAlbum && $data->is_nsfw && Configs::getValueAsBool('nsfw_blur');
		$this->thumb = $data->thumb;
		$this->title = $data->title;
		$this->is_public = isset($data->is_public) && $data->is_public;
		$this->require_link = $data instanceof BaseAlbum && $data->requires_link;
		$this->has_password = $data instanceof BaseAlbum && $data->has_password;
		$this->is_tag_album = $data instanceof TagAlbum;
		$this->has_cover_id = $data instanceof AlbumModel && $data->cover_id !== null && $data->cover_id === $data->thumb->id;
		$this->has_subalbum = $data instanceof AlbumModel && !$data->isLeaf();
	}

	public function render()
	{
		return view('components.molecules.album');
	}
}