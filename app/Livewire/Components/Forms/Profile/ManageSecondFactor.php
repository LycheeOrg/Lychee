<?php

declare(strict_types=1);

namespace App\Livewire\Components\Forms\Profile;

use App\Exceptions\UnauthenticatedException;
use App\Exceptions\UnauthorizedException;
use App\Livewire\Traits\Notify;
use App\Livewire\Traits\UseValidator;
use App\Models\User;
use App\Policies\UserPolicy;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Laragear\WebAuthn\Models\WebAuthnCredential;
use Livewire\Attributes\Locked;
use Livewire\Component;

class ManageSecondFactor extends Component
{
	use Notify;
	use UseValidator;

	/**
	 * Credential used.
	 *
	 * @var WebAuthnCredential
	 */
	public WebAuthnCredential $credential;

	#[Locked] public bool $has_error = false;
	/** @var string alias to rename the credentials. By default we provide the first parts of the ID */
	public string $alias; // ! wired

	/**
	 * @return array<string,string|array<int,string|\Illuminate\Contracts\Validation\ValidationRule|\Illuminate\Validation\Rules\Enum>>
	 */
	public function rules(): array
	{
		return ['alias' => 'required|string|min:5|max:255'];
	}

	/**
	 * Just mount the component with the required WebAuthn Credentials.
	 *
	 * @param WebAuthnCredential $credential
	 *
	 * @return void
	 */
	public function mount(WebAuthnCredential $credential): void
	{
		$this->authorize(UserPolicy::CAN_EDIT, [User::class]);

		if ($credential->authenticatable_id !== (Auth::id() ?? throw new UnauthenticatedException())) {
			throw new UnauthorizedException();
		}

		$this->credential = $credential;
		$this->alias = $credential->alias ?? Str::substr($credential->id, 0, 30);
	}

	/**
	 * Rendering of the front-end.
	 *
	 * @return View
	 */
	public function render(): View
	{
		return view('livewire.forms.profile.manage-second-factor');
	}

	/**
	 * This runs after a wired property is updated.
	 *
	 * @param mixed $field
	 * @param mixed $value
	 *
	 * @return void
	 */
	public function updated($field, $value): void
	{
		if (!$this->areValid($this->rules())) {
			$this->has_error = true;

			return;
		}

		$this->has_error = false;
		$this->credential->alias = $this->alias;
		$this->credential->save();
		$this->notify(__('lychee.CHANGE_SUCCESS'));
	}
}
