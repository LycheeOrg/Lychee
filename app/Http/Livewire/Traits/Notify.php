<?php

namespace App\Http\Livewire\Traits;

/**
 * Send a notification to the Front End.
 */
trait Notify
{
	/**
	 * Send message to front-end, it will be displayed in the top right of the window.
	 *
	 * @param string $message to send
	 *
	 * @return void
	 */
	public function notify(string $message)
	{
		$this->dispatchBrowserEvent('notify', $message);
	}
}