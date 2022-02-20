<?php

namespace App\View\Components;

use App\Contracts\AbstractAlbum;
use App\Models\Configs;
use App\Models\Extensions\Thumb;
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
		$this->is_nsfw = isset($data->is_nsfw) && $data->is_nsfw && Configs::get_value('nsfw_blur', '1') == '1';
		$this->thumb = $data->thumb;
		$this->title = $data->title;
		$this->is_public = isset($data->is_public) && $data->is_public;
		$this->require_link = $data->requires_link;
		$this->has_password = isset($data->has_password) && $data->has_password;
		$this->is_tag_album = isset($data->tag_album) && $data->tag_album == '1';
		$this->has_cover_id = isset($data->cover_id) && isset($data->thumb->id) && $data->cover_id == $data->thumb->id;
		$this->has_subalbum = (isset($data->has_albums) && !$data->has_albums) || (isset($data->albums) && $data->albums->count() > 0) || (isset($data->_lft) && $data->_lft + 1 < $data->_rgt);
	}

	public function render()
	{
		return view('components.molecules.album');
	}
}