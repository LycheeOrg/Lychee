<?php

namespace App\View\Components;

use App\Models\Configs;
use App\Models\Photo as PhotoModel;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Illuminate\View\Component;

class Photo extends Component
{
	public $class = '';

	public $album_id = '';
	public $photo_id = '';

	public $show_live = false;
	public $show_play = false;
	public $show_placeholder = false;

	public $title = '';
	public $takedate = '';
	public $sysdate = '';

	public $src = '';
	public $srcset = '';
	public $srcset2x = '';

	public $layout = false;
	public int $_w = PhotoModel::THUMBNAIL_DIM;
	public int $_h = PhotoModel::THUMBNAIL_DIM;

	/**
	 * Create a new component instance.
	 *
	 * @return void
	 */
	public function __construct(array $data)
	{
		$this->album_id = $data['album'];
		$this->photo_id = $data['id'];
		$this->title = $data['title'];
		$this->takedate = $data['takedate'];
		$this->sysdate = $data['sysdate'];
		$this->star = $data['star'] == '1';
		$this->public = $data['public'] == '1';

		$isVideo = Str::contains($data['type'], 'video');
		$isRaw = Str::contains($data['type'], 'raw');
		$isLivePhoto = filled($data['livePhotoUrl']);

		$this->class = '';
		$this->class .= $isVideo ? ' video' : '';
		$this->class .= $isLivePhoto ? ' livephoto' : '';

		$this->layout = Configs::get_value('layout', '0') == '0';

		if ($data['thumbUrl'] == 'uploads/thumb/') {
			$this->show_live = $isLivePhoto;
			$this->show_play = $isVideo;
			$this->show_placeholder = $isRaw;
		}

		$dim = '';
		$dim2x = '';
		$thumb2x = '';

		// TODO: The class Photo for the database model does not anymore contain the attributes `small`, `small_dim`, etc. Probably this code needs some fix/refactoring, too. However, where is this method invoked and what is the structure of the passed `data` array? (Could find any invocation.)
		if ($this->layout) {
			$thumb = $data['thumbUrl'];
			$thumb2x = $data['thumb2x'];
		} elseif ($data['small'] !== '') {
			$thumb = $data['small'];
			$thumb2x = $data['small2x'];
			$wh = explode('x', $data['small_dim']);
			$this->_w = intval($wh[0]);
			$this->_h = intval($wh[1]);
			$dim = intval($data['small_dim']);
			$dim2x = intval($data['small2x_dim']);
		} elseif ($data['medium'] !== '') {
			$thumb = $data['medium'];
			$thumb2x = $data['medium2x'];
			$wh = explode('x', $data['medium_dim']);
			$this->_w = intval($wh[0]);
			$this->_h = intval($wh[1]);
			$dim = intval($data['medium_dim']);
			$dim2x = intval($data['medium2x_dim']);
		} elseif (!$isVideo) {
			// Fallback for images with no small or medium.
			$thumb = $data['url'];
			$this->_w = intval($data['width']);
			$this->_h = intval($data['height']);
		} else {
			// Fallback for videos with no small (the case of no thumb is handled else where).
			$this->class = 'video';
			$thumb = $data['thumbUrl'];
			$thumb2x = $data['thumb2x'];
			$dim = (string) \App\Models\Photo::THUMBNAIL_DIM;
			$dim2x = (string) \App\Models\Photo::THUMBNAIL2X_DIM;
		}

		$this->src = "src='" . URL::asset('img/placeholder.png') . "'";
		$this->srcset = "data-src='" . URL::asset($thumb) . "'";
		$thumb2x_src = '';

		if ($this->layout) {
			$thumb2x_src = URL::asset($thumb2x) . ' 2x';
		} else {
			$thumb2x_src = URL::asset($thumb) . ' ' . $dim . 'w, ';
			$thumb2x_src .= URL::asset($thumb2x) . ' ' . $dim2x . 'w';
		}

		$this->srcset2x = $thumb2x != '' ? "data-srcset='" . $thumb2x_src . "'" : '';
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
