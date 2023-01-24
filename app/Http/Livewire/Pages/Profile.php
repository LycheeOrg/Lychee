<?php

namespace App\Http\Livewire\Pages;

use App\Enum\Livewire\PageMode;
use App\Exceptions\ConfigurationKeyMissingException;
use App\Models\Configs;
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
	public PageMode $mode = PageMode::PROFILE;

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
}
