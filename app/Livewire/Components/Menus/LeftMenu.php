<?php

namespace App\Livewire\Components\Menus;

use App\Contracts\Livewire\Openable;
use App\Livewire\Traits\InteractWithModal;
use App\Livewire\Traits\UseOpenable;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\View\View;
use Livewire\Component;

/**
 * This is the Left menu component.
 * In here we will manage the different links:
 * - settings.
 * - User profile (New!)
 * - users
 * - U2F (redirect to user profile)
 * - sharing
 * - Diagnostics
 * - Logs
 * - About
 * - logout.
 */
class LeftMenu extends Component implements Openable
{
	use InteractWithModal;
	use UseOpenable;

	/**
	 * Method called from user-land.
	 * Log out the user.
	 */
	public function logout()
	{
		Auth::logout();
		Session::flush();

		$this->close();
		$this->redirect(route('livewire-gallery'), navigate: false);
		// $this->dispatch('reloadPage');
	}

	/**
	 * Render the Left menu.
	 *
	 * @return View
	 */
	public function render(): View
	{
		return view('livewire.components.left-menu');
	}

	/**
	 * Open a about modal box.
	 * TODO Consider moving this directly to Blade.
	 *
	 * @return void
	 */
	public function openAboutModal(): void
	{
		$this->openClosableModal('modals.about', __('lychee.CLOSE'));
	}
}
