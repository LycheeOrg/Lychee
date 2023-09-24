<?php

namespace App\Livewire\Components\Forms\Profile;

use App\Actions\User\TokenDisable;
use App\Actions\User\TokenReset;
use App\Exceptions\UnauthenticatedException;
use App\Livewire\Traits\InteractWithModal;
use App\Models\User;
use App\Policies\UserPolicy;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Locked;
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
	#[Locked] public string $token = '';
	// token is disabled
	#[Locked] public bool $isDisabled;
	private TokenReset $tokenReset;
	private TokenDisable $tokenDisable;

	public function boot(): void
	{
		$this->tokenReset = resolve(TokenReset::class);
		$this->tokenDisable = resolve(TokenDisable::class);
	}

	/**
	 * Mount the current data of the user.
	 * $token is kept empty in order to avoid revealing the data.
	 *
	 * @return void
	 */
	public function mount(): void
	{
		$user = Auth::user() ?? throw new UnauthenticatedException();

		$this->token = __('lychee.TOKEN_NOT_AVAILABLE');
		$this->isDisabled = true;

		if ($user->token === null) {
			$this->token = __('lychee.DISABLED_TOKEN_STATUS_MSG');
		}
	}

	/**
	 * Renders the modal content.
	 *
	 * @return View
	 */
	public function render(): View
	{
		return view('livewire.forms.profile.get-api-token');
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
	 * @return void
	 */
	public function resetToken(): void
	{
		/**
		 * Authorize the request.
		 */
		$this->authorize(UserPolicy::CAN_EDIT, [User::class]);

		$this->token = $this->tokenReset->do();
		$this->isDisabled = false;
	}

	/**
	 * Method call from front-end to disable the token.
	 * We simply erase the current one.
	 *
	 * @return void
	 */
	public function disableToken(): void
	{
		/**
		 * Authorize the request.
		 */
		$this->authorize(UserPolicy::CAN_EDIT, [User::class]);

		$this->tokenDisable->do();
		$this->token = __('lychee.DISABLED_TOKEN_STATUS_MSG');
		$this->isDisabled = true;
	}
}