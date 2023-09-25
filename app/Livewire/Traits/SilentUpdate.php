<?php

namespace App\Livewire\Traits;

use Livewire\Attributes\Renderless;

/**
 * This trait provides an empty function. This is to be called from the front end
 * when data needs to be synchronized without a rendering (e.g. triggered by computed properties).
 */
trait SilentUpdate
{
	/**
	 * Send message to front-end, it will be displayed in the top right of the window.
	 *
	 * @return void
	 */
	#[Renderless]
	public function silentUpdate(): void
	{
		// DO NOTHING!
	}
}