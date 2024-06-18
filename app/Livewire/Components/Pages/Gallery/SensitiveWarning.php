<?php

declare(strict_types=1);

namespace App\Livewire\Components\Pages\Gallery;

use App\Contracts\Livewire\Openable;
use App\Contracts\Models\AbstractAlbum;
use App\Livewire\Traits\UseOpenable;
use App\Models\Album;
use App\Models\Configs;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Livewire\Component;

/**
 * This is the overlay displaying the NSFW warning.
 */
class SensitiveWarning extends Component implements Openable
{
	use UseOpenable;

	// Text to be displayed. THIS IS HTML UNSANITIZED
	public string $text;
	public bool $isBlurred;

	/**
	 * Prepare the warning if required by the album.
	 * - We also give the ability to override the text in LANG by the user.
	 *
	 * @param AbstractAlbum|null $album
	 *
	 * @return void
	 */
	public function mount(?AbstractAlbum $album = null): void
	{
		$this->text = Configs::getValueAsString('nsfw_banner_override');
		$this->isBlurred = Configs::getValueAsBool('nsfw_banner_blur_backdrop');

		// TODO: remember if was open or not.

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
