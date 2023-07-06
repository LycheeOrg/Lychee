<?php

namespace App\Http\Livewire\Forms\Profile;

use App\Actions\User\TokenDisable;
use App\Actions\User\TokenReset;
use App\Http\Livewire\Traits\InteractWithModal;
use App\Models\User;
use App\Policies\UserPolicy;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

/**
 * Retrieve the API token for the current user.
 * This is the Modal integration.
 */
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

	/**
	 * Mount the current data of the user.
	 * $token is kept empty in order to avoid revealing the data.
	 *
	 * @return void
	 */
	public function mount(): void
	{
		$user = Auth::user();

		$this->isDisabled = $user->token === null;
		$this->isHidden = true;
	}

	/**
	 * Renders the modal content.
	 *
	 * @return View
	 */
	public function render(): View
	{
		return view('livewire.forms.profile.form-get-api-token');
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

	/**
	 * Method call from front-end to reset the Token.
	 * We generate a new one on the fly and display it.
	 *
	 * @param TokenReset $tokenReset
	 *
	 * @return void
	 */
	public function resetToken(TokenReset $tokenReset): void
	{
		/**
		 * Authorize the request.
		 */
		$this->authorize(UserPolicy::CAN_EDIT, [User::class]);

		$this->token = $tokenReset->do();
		$this->isDisabled = false;
		$this->isHidden = false;
	}

	/**
	 * Method call from front-end to disable the token.
	 * We simply erase the current one.
	 *
	 * @param TokenDisable $tokenDisable
	 *
	 * @return void
	 */
	public function disableToken(TokenDisable $tokenDisable): void
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