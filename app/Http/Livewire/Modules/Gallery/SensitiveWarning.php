<?php

namespace App\Http\Livewire\Modules\Gallery;

use App\Contracts\Models\AbstractAlbum;
use App\Facades\Lang;
use App\Http\Livewire\Components\Base\Openable;
use App\Models\Album;
use App\Models\Configs;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class SensitiveWarning extends Openable
{
	public string $text;

	public function mount(AbstractAlbum $album = null)
	{
		$override = Configs::getValueAsString('nsfw_banner_override');
		$this->text = $override !== '' ? $override : Lang::get('NSFW_BANNER');

		if ($album instanceof Album) {
			$this->isOpen = $album->is_nsfw;

			if (Auth::user()?->may_administrate === true) {
				$this->isOpen &= Configs::getValueAsBool('nsfw_warning_admin');
			} else {
				$this->isOpen &= Configs::getValueAsBool('nsfw_warning');
			}
		}
	}

	/**
	 * Render the associated view.
	 *
	 * @return View
	 *
	 * @throws BindingResolutionException
	 */
	public function render(): View
	{
		return view('livewire.modules.gallery.sensitive-warning');
	}
}
