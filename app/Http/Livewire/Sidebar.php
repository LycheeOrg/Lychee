<?php

namespace App\Http\Livewire;

use AccessControl;
use App\Models\Album;
use App\Models\Photo;
use DebugBar;
use Lang;
use Livewire\Component;

class Sidebar extends Component
{
	public $title = '';
	public $data = [];
	/**
	 * @var Photo
	 */
	public $photo;
	/**
	 * @var Album
	 */
	public $album;

	public function mount(Album $album = null, Photo $photo = null)
	{
		$this->album = $album;
		DebugBar::notice($album);
		$this->photo = $photo;
	}

	public function generateAlbumStructure()
	{
		$this->title = Lang::get('ALBUM_ABOUT');
		$this->data = [];
		$basic = new \stdClass();
		$basic->title = Lang::get('ALBUM_BASICS');
		$basic->content = [];

		$basic->content[] = ['head' => Lang::get('ALBUM_TITLE'), 'value' => $this->album->title];
		if ($this->album->description != '') {
			['head' => Lang::get('ALBUM_DESCRIPTION'), 'value' => $this->album->description];
		}

		if ($this->album->is_tag_album()) {
			$basic->content[] = ['head' => Lang::get('ALBUM_SHOW_TAGS'), 'value' => $this->album->showtags];
		}

		$album = new \stdClass();
		$album->title = Lang::get('ALBUM_ALBUM');
		$album->content = [
			['head' => Lang::get('ALBUM_CREATED'), 'value' => $this->album->created_at->format('F Y')],
		];
		if ($this->album->children->count() > 0) {
			$album->content[] = ['head' => Lang::get('ALBUM_SUBALBUMS'), 'value' => $this->album->children->count()];
		}

		$counted = $this->album->photos->countBy(function (Photo $photo) {
			return $photo->isVideo() ? 'videos' : 'photos';
		})->all();
		if (isset($counted['photos'])) {
			$album->content[] = ['head' => Lang::get('ALBUM_IMAGES'), 'value' => $counted['photos']];
		}
		if (isset($counted['videos'])) {
			$album->content[] = ['head' => Lang::get('ALBUM_VIDEOS'), 'value' => $counted['videos']];
		}
		if (isset($counted['photos'])) {
			if ($this->album->sorting_col === '') {
				$sorting = Lang::get('DEFAULT');
			} else {
				$sorting = $this->album->sorting_col + ' ' + $this->album->sorting_order;
			}

			$album->content[] = ['head' => Lang::get('ALBUM_ORDERING'), 'value' => $sorting];
		}

		$share = new \stdClass();
		$share->title = Lang::get('ALBUM_SHARING');
		$_public = $this->album->is_public() ? Lang::get('ALBUM_SHR_YES') : Lang::get('ALBUM_SHR_NO');
		$_hidden = $this->album->viewable == '0' ? Lang::get('ALBUM_SHR_YES') : Lang::get('ALBUM_SHR_NO'); // TODO : double check;
		$_downloadable = $this->album->is_downloadable() ? Lang::get('ALBUM_SHR_YES') : Lang::get('ALBUM_SHR_NO');
		$_share_button_visible = $this->album->is_share_button_visible() ? Lang::get('ALBUM_SHR_YES') : Lang::get('ALBUM_SHR_NO');
		$_password = $this->album->password != '' ? Lang::get('ALBUM_SHR_YES') : Lang::get('ALBUM_SHR_NO');
		$share->content = [
			['head' => Lang::get('ALBUM_PUBLIC'), 'value' => $_public],
			['head' => Lang::get('ALBUM_HIDDEN'), 'value' => $_hidden],
			['head' => Lang::get('ALBUM_DOWNLOADABLE'), 'value' => $_downloadable],
			['head' => Lang::get('ALBUM_SHARE_BUTTON_VISIBLE'), 'value' => $_share_button_visible],
			['head' => Lang::get('ALBUM_PASSWORD'), 'value' => $_password],
		];
		if ($this->album->owner_id != null) {
			$share->content[] = ['head' => Lang::get('ALBUM_OWNER'), 'value' => $this->album->owner->name()];
		}

		$license = new \stdClass();
		$license->title = Lang::get('ALBUM_REUSE');
		$license->content = [
			['head' => Lang::get('ALBUM_LICENSE'), 'value' => $this->album->get_license()],
		];

		$this->data = [$basic, $album, $license];

		if (AccessControl::is_logged_in()) {
			$this->data[] = $share;
		}
	}

	public function render()
	{
		if ($this->album != null) {
			$this->generateAlbumStructure();
		} else {
			$this->data = [];
			$this->title = '';
		}

		return view('livewire.sidebar');
	}
}