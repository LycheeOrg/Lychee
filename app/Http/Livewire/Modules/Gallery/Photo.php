<?php

namespace App\Http\Livewire\Modules\Gallery;

use App\Models\Photo as PhotoModel;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\View\View;
use Livewire\Component;

/**
 * Similar to the Album module, this takes care of displaying a single photo.
 */
class Photo extends Component
{
	/** @var PhotoModel Said photo to be displayed */
	public PhotoModel $photo;

	// ! Will be used later
	public bool $visibleControls = false;

	/**
	 * Render the associated view.
	 *
	 * @return View
	 *
	 * @throws BindingResolutionException
	 */
	public function render(): View
	{
		return view('livewire.modules.gallery.photo');
	}
}
