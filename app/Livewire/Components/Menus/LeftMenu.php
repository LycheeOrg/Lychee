<?php

namespace App\Livewire\Components\Menus;

use App\Contracts\Livewire\Openable;
use App\Livewire\Traits\InteractWithModal;
use App\Livewire\Traits\UseOpenable;
use App\Models\Configs;
use App\Policies\SettingsPolicy;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\URL;
use Illuminate\View\View;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
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

	#[Locked] public bool $has_dev_tools = false;
	#[Locked] public ?string $clockwork_url = null;
	#[Locked] public ?string $doc_api_url = null;
	/**
	 * Method called from user-land.
	 * Log out the user.
	 */
	public function logout(): void
	{
		Auth::logout();
		Session::flush();

		// Hard redirect & refresh instead of using Livewire.
		// Enures the full reloading of the page.
		redirect(route('livewire-gallery'));
	}

	/**
	 * Render the Left menu.
	 *
	 * @return View
	 */
	#[On('reloadPage')]
	public function render(): View
	{
		$this->loadDevMenu();

		return view('livewire.components.left-menu');
	}


	/**
	 * Open the Context Menu.
	 *
	 * @return void
	 */
	#[On('openLeftMenu')]
	public function openLeftMenu(): void
	{
		$this->open();
	}

	/**
	 * Close the LeftMenu component.
	 *
	 * @return void
	 */
	#[On('closeLeftMenu')]
	public function closeLeftMenu(): void
	{
		$this->close();
	}

	/**
	 * Toggle the LeftMenu component.
	 *
	 * @return void
	 */
	#[On('toggleLeftMenu')]
	public function toggleLeftMenu(): void
	{
		$this->toggle();
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

	/**
	 * We load some data about debuging tools section.
	 *
	 * @return void
	 */
	private function loadDevMenu(): void
	{
		$this->has_dev_tools = Gate::check(SettingsPolicy::CAN_ACCESS_DEV_TOOLS, [Configs::class]);
		if (!$this->has_dev_tools) {
			return;
		}

		// Defining clockwork URL
		$clockWorkEnabled = config('clockwork.enable') === true || (config('app.debug') === true && config('clockwork.enable') === null);
		$clockWorkWeb = config('clockwork.web');
		if ($clockWorkEnabled && $clockWorkWeb === true || is_string($clockWorkWeb)) {
			$this->clockwork_url = $clockWorkWeb === true ? URL::asset('clockwork/app') : $clockWorkWeb . '/app';
		}

		// API documentation
		$this->doc_api_url = Route::has('scramble.docs.api') ? route('scramble.docs.api') : null;

		// Double check to avoid showing an empty section.
		$this->has_dev_tools = $this->doc_api_url !== null && $this->clockwork_url !== null;
	}
}
