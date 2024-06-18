<?php

declare(strict_types=1);

namespace App\Livewire\Components\Pages;

use App\Models\Configs;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Laragear\WebAuthn\Models\WebAuthnCredential;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Livewire\Component;

/**
 * Login page.
 */
class Login extends Component
{
	#[Locked] public string $title;
	#[Locked] public bool $can_use_2fa;
	/**
	 * @return void
	 */
	public function mount(): void
	{
		if (!Configs::getValueAsBool('login_required') || Auth::user() !== null) {
			redirect(route('livewire-gallery'));
		}
		$this->title = Configs::getValueAsString('site_title');
		$this->can_use_2fa = !Auth::check() && (WebAuthnCredential::query()->whereNull('disabled_at')->count() > 0);
	}

	/**
	 * Rendering of the front-end.
	 *
	 * @return View
	 */
	public function render(): View
	{
		return view('livewire.pages.login');
	}

	public function getIsLoginLeftProperty(): bool
	{
		return Configs::getValueAsString('login_button_position') === 'left';
	}

	#[On('reloadPage')]
	public function reloadPage(): void
	{
		redirect(route('livewire-gallery'));
	}
}
