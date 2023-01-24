<?php

namespace App\Http\Livewire\Modules\Gallery;

use App\Contracts\Models\AbstractAlbum;
use App\Facades\Lang;
use App\Http\Livewire\Components\Base\Openable;
use App\Models\Album;
use App\Models\Configs;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/**
 * This is the overlay displaying the NSFW warning.
 */
class SensitiveWarning extends Openable
{
	// Text to be displayed. THIS IS HTML UNSANITIZED
	public string $text;

	/**
	 * Prepare the warning if required by the album.
	 * - We also give the ability to override the text in LANG by the user.
	 *
	 * @param AbstractAlbum|null $album
	 *
	 * @return void
	 */
	public function mount(AbstractAlbum $album = null): void
	{
		$override = Configs::getValueAsString('nsfw_banner_override');
		$this->text = $override !== '' ? $override : __('lychee.NSFW_BANNER');

		if ($album instanceof Album) {
			$this->isOpen = $album->is_nsfw;

			if (Auth::user()?->may_administrate === true) {
				$this->isOpen = $this->isOpen && Configs::getValueAsBool('nsfw_warning_admin');
			} else {
				$this->isOpen = $this->isOpen && Configs::getValueAsBool('nsfw_warning');
			}
		}
	}

	/**
	 * Render the associated view.
	 *
	 * @return View
	 */
	public function render(): View
	{
		return view('livewire.modules.gallery.sensitive-warning');
	}
}
