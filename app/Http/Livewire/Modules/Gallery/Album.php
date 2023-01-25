<?php

namespace App\Http\Livewire\Modules\Gallery;

use App\Contracts\Models\AbstractAlbum;
use App\Enum\Livewire\AlbumMode;
use App\Http\Livewire\Traits\AlbumProperty;
use App\Models\Album as ModelsAlbum;
use App\Models\Configs;
use App\Models\Extensions\BaseAlbum;
use App\SmartAlbums\BaseSmartAlbum;
use Illuminate\View\View;
use Livewire\Component;

/**
 * Album sub module.
 *
 * We just load the layout from config and render.
 * The variable $album is automatically mounted from the Livewire call
 */
class Album extends Component
{
	/**
	 * Because AbstractAlbum is an Interface, it is not possible to make it
	 * and attribute of a Livewire Component as on the "way back" we do not know
	 * in what kind of AbstractAlbum we need to cast it back.
	 *
	 * One way to solve this would actually be to create either an WireableAlbum container
	 * Or to use a computed property on the model. We chose the later.
	 */
	use AlbumProperty;

	public AlbumMode $layout;
	public ?BaseAlbum $baseAlbum = null;
	public ?BaseSmartAlbum $smartAlbum = null;

	/**
	 * Listeners for roloading the page.
	 *
	 * @var string[]
	 */
	protected $listeners = ['reload'];

	/**
	 * Rendering of the blade template.
	 *
	 * @return View
	 */
	public function render(): View
	{
		$this->layout = Configs::getValueAsEnum('layout', AlbumMode::class);

		return view('livewire.modules.gallery.album');
	}

	/**
	 * Reload the data.
	 *
	 * @return void
	 */
	public function reload(): void
	{
		if ($this->baseAlbum instanceof ModelsAlbum) {
			$this->baseAlbum = ModelsAlbum::findOrFail($this->baseAlbum->id);
		}
	}
}
