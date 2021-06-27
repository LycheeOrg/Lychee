<?php

namespace App\View\Components;

use App\Models\Configs;
use App\Models\Extensions\SizeVariant;
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
	public int $_w = SizeVariant::THUMBNAIL_DIM;
	public int $_h = SizeVariant::THUMBNAIL_DIM;

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
		$this->takedate = $data['taken_at'];
		$this->created_at = $data['created_at'];
		$this->star = $data['star'];
		$this->public = $data['public'];

		$isVideo = Str::contains($data['type'], 'video');
		$isRaw = Str::contains($data['type'], 'raw');
		$isLivePhoto = filled($data['live_Photo_filename']);

		$this->class = '';
		$this->class .= $isVideo ? ' video' : '';
		$this->class .= $isLivePhoto ? ' livephoto' : '';

		$this->layout = Configs::get_value('layout', '0') == '0';

		// TODO: Don't hardcode paths
		if ($data['sizeVariants']['thumb']['url'] == 'uploads/thumb/') {
			$this->show_live = $isLivePhoto;
			$this->show_play = $isVideo;
			$this->show_placeholder = $isRaw;
		}

		$dim = '';
		$dim2x = '';
		$thumb2x = '';

		// TODO: The class Photo for the database model does not anymore contain the attributes `small`, `small_dim`, etc.
		// Probably this code needs some fix/refactoring, too. However, where is this method invoked and
		// what is the structure of the passed `data` array? (Could find any invocation.)
		if ($this->layout) {
			$thumb = $data['sizeVariants']['thumb']['url'];
			$thumb2x = $data['sizeVariants']['thumb2x']['url'];
		} elseif ($data['sizeVariants']['small'] !== null) {
			$thumb = $data['sizeVariants']['small']['url'];
			$thumb2x = $data['sizeVariants']['small2x']['url'] ?? '';
			$this->_w = $data['sizeVariants']['small']['width'];
			$this->_h = $data['sizeVariants']['small']['height'];
			$dim = $data['sizeVariants']['small']['width'];
			$dim2x = $data['sizeVariants']['small2x']['width'] ?? 0;
		} elseif ($data['sizeVariants']['medium'] !== null) {
			$thumb = $data['sizeVariants']['medium']['url'];
			$thumb2x = $data['sizeVariants']['medium2x']['url'] ?? '';
			$this->_w = $data['sizeVariants']['medium']['width'];
			$this->_h = $data['sizeVariants']['medium']['height'];
			$dim = $data['sizeVariants']['medium']['width'];
			$dim2x = $data['sizeVariants']['medium2x']['width'] ?? 0;
		} elseif (!$isVideo) {
			// Fallback for images with no small or medium.
			$thumb = $data['url'];
			$this->_w = $data['width'];
			$this->_h = $data['height'];
		} else {
			// Fallback for videos with no small (the case of no thumb is handled else where).
			$this->class = 'video';
			$thumb = $data['sizeVariants']['thumb']['url'];
			$thumb2x = $data['sizeVariants']['thumb2x']['url'];
			$dim = (string) PhotoModel::THUMBNAIL_DIM;
			$dim2x = (string) PhotoModel::THUMBNAIL2X_DIM;
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
