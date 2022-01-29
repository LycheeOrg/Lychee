<?php

namespace App\View\Components;

use App\Models\Configs;
use App\Models\Photo as ModelsPhoto;
use App\Models\SizeVariant;
use Illuminate\Support\Facades\URL;
use Illuminate\View\Component;

class Photo extends Component
{
	public string $class = '';

	public string $album_id = '';
	public string $photo_id = '';

	public bool $show_live = false;
	public bool $show_play = false;
	public bool $show_placeholder = false;

	public string $title = '';
	public string $taken_at = '';
	public string $created_at = '';
	public bool $is_starred = false;
	public bool $is_public = false;

	public string $src = '';
	public string $srcset = '';
	public string $srcset2x = '';

	public bool $layout = false;
	public int $_w = 200;
	public int $_h = 200;

	/**
	 * Create a new component instance.
	 *
	 * @return void
	 */
	public function __construct(ModelsPhoto $data)
	{
		$this->album_id = $data->album;
		$this->photo_id = $data->id;
		$this->title = $data->title;
		$this->taken_at = $data->taken_at ?? '';
		$this->created_at = $data->created_at;
		$this->is_starred = $data->is_starred;
		$this->is_public = $data->is_public;

		$this->class = '';
		$this->class .= $data->isVideo() ? ' video' : '';
		$this->class .= $data->isLivePhoto() ? ' livephoto' : '';

		$this->layout = Configs::get_value('layout', '0') == '0';

		// dd($data->size_variants->thumb);
		// TODO: Don't hardcode paths
		if ($data->size_variants->getSizeVariant(SizeVariant::THUMB)->url == 'uploads/thumb/') {
			$this->show_live = $data->isLivePhoto();
			$this->show_play = $data->isVideo();
			$this->show_placeholder = $data->isRawy();
		}

		$dim = '';
		$dim2x = '';
		$thumb2x = '';

		$thumb = $data->size_variants->getSizeVariant(SizeVariant::THUMB);
		$thumb2x = $data->size_variants->getSizeVariant(SizeVariant::THUMB2X);
		$small = $data->size_variants->getSizeVariant(SizeVariant::SMALL);
		$small2x = $data->size_variants->getSizeVariant(SizeVariant::SMALL2X);
		$medium = $data->size_variants->getSizeVariant(SizeVariant::MEDIUM);
		$medium2x = $data->size_variants->getSizeVariant(SizeVariant::MEDIUM2X);
		$original = $data->size_variants->getSizeVariant(SizeVariant::ORIGINAL);

		// Probably this code needs some fix/refactoring, too. However, where is this method invoked and
		// what is the structure of the passed `data` array? (Could find any invocation.)
		if ($this->layout) {
			$thumbUrl = $thumb->url;
			$thumb2xUrl = $thumb2x->url;
		} elseif ($small !== null) {
			$thumbUrl = $small->url;
			$thumb2xUrl = $small2x->url ?? '';
			$this->_w = $small->width;
			$this->_h = $small->height;
			$dim = $small->width;
			$dim2x = $small2x->width ?? 0;
		} elseif ($medium !== null) {
			$thumbUrl = $medium->url;
			$thumb2xUrl = $medium2x->url ?? '';
			$this->_w = $medium->width;
			$this->_h = $medium->height;
			$dim = $medium->width;
			$dim2x = $medium2x->width ?? 0;
		} elseif (!$data->isVideo()) {
			// Fallback for images with no small or medium.
			$thumbUrl = $original->url;
			$this->_w = $original->width;
			$this->_h = $original->height;
		} else {
			// Fallback for videos with no small (the case of no thumb is handled else where).
			$this->class = 'video';
			$thumbUrl = $thumb->url;
			$thumb2xUrl = $thumb2x->url;
			$dim = (string) 200;
			$dim2x = (string) 200;
		}

		$this->src = "src='" . URL::asset('img/placeholder.png') . "'";
		$this->srcset = "data-src='" . URL::asset($thumbUrl) . "'";
		$thumb2x_src = '';

		if ($this->layout) {
			$thumb2x_src = URL::asset($thumb2xUrl) . ' 2x';
		} else {
			$thumb2x_src = URL::asset($thumbUrl) . ' ' . $dim . 'w, ';
			$thumb2x_src .= URL::asset($thumb2xUrl) . ' ' . $dim2x . 'w';
		}

		$this->srcset2x = $thumb2xUrl != '' ? "data-srcset='" . $thumb2x_src . "'" : '';
	}

	/**
	 * Get the view / contents that represent the component.
	 *
	 * @return \Illuminate\Contracts\View\View|\Closure|string
	 */
	public function render()
	{
		return view('components.photo');
	}
}
