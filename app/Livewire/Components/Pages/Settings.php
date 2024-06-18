<?php

declare(strict_types=1);

namespace App\Livewire\Components\Pages;

use App\Exceptions\InsufficientFilesystemPermissions;
use App\Livewire\Components\Menus\LeftMenu;
use App\Livewire\Traits\InteractWithModal;
use App\Livewire\Traits\Notify;
use App\Models\Configs;
use App\Policies\SettingsPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Livewire\Component;

class Settings extends Component
{
	/*
	* Add interaction with modal
	*/
	use InteractWithModal;
	use Notify;

	public string $css;
	public string $js;

	/**
	 * Mount the component of the front-end.
	 *
	 * @return void
	 */
	public function mount(): void
	{
		Gate::authorize(SettingsPolicy::CAN_EDIT, [Configs::class]);
		$this->css = Storage::disk('dist')->get('user.css') ?? '';
		$this->js = Storage::disk('dist')->get('custom.js') ?? '';
	}

	/**
	 * Rendering of the front-end.
	 *
	 * @return View
	 */
	public function render(): View
	{
		return view('livewire.pages.settings');
	}

	public function back(): mixed
	{
		$this->dispatch('closeLeftMenu')->to(LeftMenu::class);

		return $this->redirect(route('livewire-gallery'), true);
	}

	/**
	 * Takes the css input text and put it into `dist/user.css`.
	 * This allows admins to actually personalize the look of their
	 * installation.
	 *
	 * @return void
	 *
	 * @throws InsufficientFilesystemPermissions
	 */
	public function setCSS(): void
	{
		Gate::authorize(SettingsPolicy::CAN_EDIT, Configs::class);

		if (Storage::disk('dist')->put('user.css', $this->css) === false) {
			if (Storage::disk('dist')->get('user.css') !== $this->css) {
				throw new InsufficientFilesystemPermissions('Could not save CSS');
			}
		}
		$this->notify(__('lychee.CHANGE_SUCCESS'));
	}

	/**
	 * Takes the js input text and put it into `dist/custom.js`.
	 * This allows admins to actually execute custom js code on their
	 * Lychee-Laravel installation.
	 *
	 * @return void
	 *
	 * @throws InsufficientFilesystemPermissions
	 */
	public function setJS(): void
	{
		Gate::authorize(SettingsPolicy::CAN_EDIT, Configs::class);

		if (Storage::disk('dist')->put('custom.js', $this->js) === false) {
			if (Storage::disk('dist')->get('custom.js') !== $this->js) {
				throw new InsufficientFilesystemPermissions('Could not save JS');
			}
		}
		$this->notify(__('lychee.CHANGE_SUCCESS'));
	}
}
