<?php

namespace App\Http\Livewire\Components;

use App\Facades\AccessControl;
use App\Http\Livewire\Components\Base\Openable;
use Illuminate\View\View;

/**
 * This is the Left menu component.
 * In here we will manage the different links:
 * - settings.
 * - logout
 * - users
 * - U2F
 * - sharing
 * - Diagnostics
 * - Logs
 * - About.
 */
class LeftMenu extends Openable
{
	/**
	 * Method called from user-land.
	 * Log out the user.
	 */
	public function logout(): \Illuminate\Routing\Redirector|\Illuminate\Http\RedirectResponse
	{
		AccessControl::logout();

		return redirect('/livewire/');
	}

	/**
	 * Render the Left menu.
	 *
	 * @return View
	 */
	public function render(): View
	{
		return view('livewire.left-menu');
	}
}
