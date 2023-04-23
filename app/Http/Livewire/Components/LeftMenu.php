<?php

namespace App\Http\Livewire\Components;

use App\Enum\Livewire\PageMode;
use App\Http\Livewire\Components\Base\Openable;
use App\Http\Livewire\Traits\InteractWithModal;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\View\View;

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
class LeftMenu extends Openable
{
	use InteractWithModal;

	/**
	 * Method called from user-land.
	 * Log out the user.
	 */
	public function logout(): \Illuminate\Routing\Redirector|\Illuminate\Http\RedirectResponse
	{
		Auth::logout();
		Session::flush();

		return redirect(route('livewire_index'));
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
		$this->openClosableModal('components.about', 'CLOSE');
	}

	/**
	 * Open Settings page.
	 * TODO Consider moving this directly to Blade.
	 *
	 * @return void
	 */
	public function openSettings(): void
	{
		$this->emitTo('index', 'openPage', PageMode::SETTINGS->value);
	}

	/**
	 * Open Log page.
	 * TODO Consider moving this directly to Blade.
	 *
	 * @return void
	 */
	public function openLogs(): void
	{
		$this->emitTo('index', 'openPage', PageMode::LOGS->value);
	}

	/**
	 * Open Diagnostic page.
	 * TODO Consider moving this directly to Blade.
	 *
	 * @return void
	 */
	public function openDiagnostics(): void
	{
		$this->emitTo('index', 'openPage', PageMode::DIAGNOSTICS->value);
	}

	/**
	 * Open Diagnostic page.
	 * TODO Consider moving this directly to Blade.
	 *
	 * @return void
	 */
	public function openUsers(): void
	{
		$this->emitTo('index', 'openPage', PageMode::USERS->value);
	}

	/**
	 * Open Diagnostic page.
	 * TODO Consider moving this directly to Blade.
	 *
	 * @return void
	 */
	public function openProfile(): void
	{
		$this->emitTo('index', 'openPage', PageMode::PROFILE->value);
	}
}
