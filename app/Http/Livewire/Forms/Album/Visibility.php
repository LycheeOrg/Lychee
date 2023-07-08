<?php

namespace App\Http\Livewire\Forms\Album;

use App\Actions\Settings\UpdateLogin;
use App\Http\RuleSets\ChangeLoginRuleSet;
use App\Models\User;
use App\Policies\UserPolicy;
use App\Rules\CurrentPasswordRule;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Visibility extends Component
{
	use AuthorizesRequests;

	public bool $is_public = false; // ! wired
	public bool $grants_full_photo_access = false; // ! wired
	public bool $is_link_required = false; // ! wired
	public bool $grants_download = false; // ! wired
	public bool $is_password_required = false; // ! wired
	public bool $is_nsfw = false;
	public string $password = '';

	/**
	 * Simply render the form.
	 *
	 * @return View
	 */
	public function render(): View
	{
		return view('livewire.forms.album.visibility');
	}

	// /**
	//  * Update Username & Password of current user.
	//  */
	// public function submit(UpdateLogin $updateLogin): void
	// {
	// 	/**
	// 	 * For the validation to work it is important that the above wired property match
	// 	 * the keys in the rules applied.
	// 	 */
	// 	$this->validate(ChangeLoginRuleSet::rules());
	// 	$this->validate(['oldPassword' => new CurrentPasswordRule()]);

	// 	/**
	// 	 * Authorize the request.
	// 	 */
	// 	$this->authorize(UserPolicy::CAN_EDIT, [User::class]);

	// 	$currentUser = $updateLogin->do(
	// 		$this->username,
	// 		$this->password,
	// 		$this->oldPassword,
	// 		request()->ip()
	// 	);

	// 	// Update the session with the new credentials of the user.
	// 	// Otherwise, the session is out-of-sync and falsely assumes the user
	// 	// to be unauthenticated upon the next request.
	// 	Auth::login($currentUser);
	// }
}
