<?php

namespace App\Livewire\Components\Pages;

use App\Exceptions\ConfigurationKeyMissingException;
use App\Models\Configs;
use App\Models\User;
use App\Policies\UserPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use Livewire\Component;

/**
 * Profile page, allows the user to:
 * - Change their username,
 * - Change their password,
 * - Set up their email for notifications
 * - Update their API Token
 * - Set up their U2F.
 */
class Profile extends Component
{
	public bool $are_notification_active = false;
	public bool $is_token_auh_active = true;

	/**
	 * Set up the profile page.
	 *
	 * @return void
	 *
	 * @throws ConfigurationKeyMissingException
	 */
	public function mount(): void
	{
		Gate::authorize(UserPolicy::CAN_EDIT, User::class);

		$this->are_notification_active = Configs::getValueAsBool('new_photos_notification');
		$this->is_token_auh_active = config('auth.guards.lychee.driver', 'session-or-token') === 'session-or-token';
	}

	/**
	 * Rendering of the front-end.
	 *
	 * @return View
	 */
	public function render(): View
	{
		return view('livewire.pages.profile');
	}

	public function back(): mixed
	{
		return $this->redirect(route('livewire-gallery'));
	}
}
