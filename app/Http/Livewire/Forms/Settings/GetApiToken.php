<?php

namespace App\Http\Livewire\Forms\Settings;

use App\Actions\User\TokenDisable;
use App\Actions\User\TokenReset;
use App\Http\Livewire\Traits\InteractWithModal;
use App\Models\User;
use App\Policies\UserPolicy;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class GetApiToken extends Component
{
	use InteractWithModal;
	use AuthorizesRequests;

	// String of the token or message
	public string $token = '';

	// token is disabled
	public bool $isDisabled;

	// token is hidden
	public bool $isHidden;

	public function mount()
	{
		$user = Auth::user();

		$this->isDisabled = !$user->has_token;
		$this->isHidden = true;
	}

	public function render()
	{
		return view('livewire.form.settings.form-get-api-token');
	}

	/**
	 * Add an handle to close the modal form from a user-land call.
	 *
	 * @return void
	 */
	public function close(): void
	{
		$this->closeModal();
	}

	public function resetToken(TokenReset $tokenReset)
	{
		/**
		 * Authorize the request.
		 */
		$this->authorize(UserPolicy::CAN_EDIT, [User::class]);

		$this->token = $tokenReset->do()->token;
		$this->isDisabled = false;
		$this->isHidden = false;
	}

	public function disableToken(TokenDisable $tokenDisable)
	{
		/**
		 * Authorize the request.
		 */
		$this->authorize(UserPolicy::CAN_EDIT, [User::class]);

		$tokenDisable->do();
		$this->token = '';
		$this->isDisabled = true;
	}
}