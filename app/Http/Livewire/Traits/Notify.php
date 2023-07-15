<?php

namespace App\Http\Livewire\Traits;

use App\Enum\Livewire\NotificationType;

/**
 * Send a notification to the Front End.
 */
trait Notify
{
	/**
	 * Send message to front-end, it will be displayed in the top right of the window.
	 *
	 * @param string           $message to send
	 * @param NotificationType $type    type of message to send
	 *
	 * @return void
	 */
	public function notify(string $message, NotificationType $type = NotificationType::SUCCESS): void
	{
		$this->dispatchBrowserEvent('notify', ['msg' => $message, 'type' => $type->value]);
	}
}