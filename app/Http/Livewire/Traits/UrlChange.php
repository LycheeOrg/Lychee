<?php

namespace App\Http\Livewire\Traits;

use App\Enum\Livewire\PageMode;
use Illuminate\Contracts\Container\BindingResolutionException;
use Symfony\Component\Routing\Exception\RouteNotFoundException;

/**
 * This trait provides a simple helper to update the URL of the browser.
 */
trait UrlChange
{
	/**
	 * Send an event to trigger updating the URL in the browser.
	 *
	 * @param PageMode $page
	 * @param string   $albumId
	 * @param string   $photoId
	 *
	 * @return void
	 *
	 * @throws BindingResolutionException
	 * @throws RouteNotFoundException
	 */
	protected function emitUrlChange(PageMode $page, string $albumId, string $photoId): void
	{
		// This ensures that the history has been updated
		$this->emit('urlChange', route('livewire_index', ['page' => $page, 'albumId' => $albumId, 'photoId' => $photoId]));
	}
}