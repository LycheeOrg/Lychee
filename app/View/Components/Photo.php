<?php

namespace App\View\Components;

use App\Models\Configs;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Illuminate\View\Component;

class Photo extends Component
{
	public $isVideo = false;
	public $isRaw = false;
	public $isLivePhoto = false;
	public $class = '';

	public $album_id = '';
	public $photo_id = '';

	public $show_live = false;
	public $show_play = false;
	public $show_placeholder = false;

	public $title = '';
	public $takedate = '';
	public $sysdate = '';

	public $thumb = '';
	public $thumb2x = '';
	public $dim = '';
	public $dim2x = '';

	public $src = '';
	public $srcset = '';
	public $srcset2x = '';

	public $layout = false;

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

		$this->isVideo = Str::contains($data['type'], 'video');
		$this->isRaw = Str::contains($data['type'], 'raw');
		$this->isLivePhoto = $data['livePhotoUrl'] != '' && $data['livePhotoUrl'] != null;

		$this->class = '';
		$this->class .= $this->isVideo ? ' video' : '';
		$this->class .= $this->isLivePhoto ? ' livephoto' : '';

		$this->layout = Configs::get_value('layout', '0') == '0';

		if ($data['thumbUrl'] == 'uploads/thumb/') {
			$this->show_live = $this->isLivePhoto;
			$this->show_play = $this->isVideo;
			$this->show_placeholder = $this->isRaw;
		}

		if ($this->layout) {
			$this->thumb = $data['thumbUrl'];
			$this->thumb2x = $data['thumb2x'];
		} elseif ($data['small'] !== '') {
			$this->thumb = $data['small'];
			$this->thumb2x = $data['small2x'];
			$this->dim = intval($data['small_dim']);
			$this->dim2x = intval($data['small2x_dim']);
		} elseif ($data['medium'] !== '') {
			$this->thumb = $data['medium'];
			$this->thumb2x = $data['medium2x'];
			$this->dim = intval($data['medium_dim']);
			$this->dim2x = intval($data['medium2x_dim']);
		} elseif (!$this->isVideo) {
			// Fallback for images with no small or medium.
			$this->thumb = $data['url'];
			$this->class = $this->isLivePhoto ? ' livephoto' : '';
		} else {
			// Fallback for videos with no small (the case of no thumb is handled else where.
			$this->class = 'video';
			$this->thumb = $data['thumbUrl'];
			$this->thumb2x = $data['thumb2x'];
			$this->dim = '200';
			$this->dim2x = '400';
		}
	}

	/**
	 * Get the view / contents that represent the component.
	 *
	 * @return \Illuminate\Contracts\View\View|\Closure|string
	 */
	public function render()
	{
		$this->src = "src='" . URL::asset('img/placeholder.png') . "'";
		$this->srcset = "data-src='" . URL::asset($this->thumb) . "'";
		$thumb2x_src = '';

		if ($this->layout) {
			$thumb2x_src = URL::asset($this->thumb2x) . ' 2x';
		} else {
			$thumb2x_src = URL::asset($this->thumb) . ' ' . $this->dim . 'w, ';
			$thumb2x_src .= URL::asset($this->thumb2x) . ' ' . $this->dim2x . 'w';
		}

		$this->srcset2x = $this->thumb2x != '' ? "data-srcset='" . $thumb2x_src . "'" : '';

		return view('components.photo');
	}
}
