<?php

namespace App\Livewire\Components\Modals;

use App\Enum\OauthProvidersType;
use App\Exceptions\Internal\QueryBuilderException;
use App\Http\RuleSets\LoginRuleSet;
use App\Metadata\Versions\FileVersion;
use App\Metadata\Versions\GitHubVersion;
use App\Metadata\Versions\InstalledVersion;
use App\Models\Configs;
use App\Models\User;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Laragear\WebAuthn\Models\WebAuthnCredential;
use Livewire\Attributes\Locked;
use Livewire\Component;

/**
 * This defines the Login Form used in modals.
 */
class Login extends Component
{
	#[Locked] public bool $is_new_release_available = false;
	#[Locked] public bool $is_git_update_available = false;
	#[Locked] public ?string $version = null;
	public ?string $username; // ! wired
	public ?string $password; // ! wired

	/**
	 * Call the parametrized rendering.
	 *
	 * @return View
	 */
	public function render(): View
	{
		return view('livewire.modals.login');
	}

	/**
	 * Mount the component.
	 *
	 * @return void
	 */
	public function mount(): void
	{
		if (!Configs::getValueAsBool('hide_version_number')) {
			$this->version = resolve(InstalledVersion::class)->getVersion()->toString();
		}

		$fileVersion = resolve(FileVersion::class);
		$gitHubVersion = resolve(GitHubVersion::class);
		if (Configs::getValueAsBool('check_for_updates')) {
			// @codeCoverageIgnoreStart
			$fileVersion->hydrate();
			$gitHubVersion->hydrate();
			// @codeCoverageIgnoreEnd
		}
		$this->is_new_release_available = !$fileVersion->isUpToDate();
		$this->is_git_update_available = !$gitHubVersion->isUpToDate();
	}

	/**
	 * Hook the submit button.
	 *
	 * @return void
	 *
	 * @throws \Throwable
	 * @throws ValidationException
	 * @throws BindingResolutionException
	 * @throws \InvalidArgumentException
	 * @throws QueryBuilderException
	 */
	public function submit(): void
	{
		// Empty error bag
		$this->resetErrorBag();

		// Call Livewire validation on the from
		$data = $this->validate(LoginRuleSet::rules());

		// apply login as admin and trigger a reload
		if (Auth::attempt(['username' => $data['username'], 'password' => $data['password']])) {
			Log::notice(__METHOD__ . ':' . __LINE__ . ' User (' . $data['username'] . ') has logged in from ' . request()->ip());
			$this->dispatch('login-close');
			$this->dispatch('reloadPage');

			return;
		}

		// Wrong login: stay on the modal and update the rendering.
		$this->addError('wrongLogin', 'Wrong login or password.');
		Log::error(__METHOD__ . ':' . __LINE__ . ' User (' . $data['username'] . ') has tried to log in from ' . request()->ip());
	}

	/**
	 * Check whether any user has a 2FA credential set.
	 *
	 * @return bool
	 */
	public function getCanUse2faProperty(): bool
	{
		return WebAuthnCredential::query()->whereNull('disabled_at')->count() > 0;
	}

	/**
	 * List the Oauth providers which are enabled.
	 *
	 * @return string[]
	 */
	public function getAvailableOauthProperty(): array
	{
		$oauthAvailable = [];

		foreach (OauthProvidersType::cases() as $oauthProvider) {
			$client_id = config('services.' . $oauthProvider->value . '.client_id');
			if ($client_id === null || $client_id === '') {
				continue;
			}

			$oauthAvailable[] = $oauthProvider->value;
		}

		return $oauthAvailable;
	}
}
