<?php

namespace App\Http\Livewire\Forms\Album;

use App\Actions\Album\SetProtectionPolicy;
use App\Actions\Settings\UpdateLogin;
use App\DTO\AlbumProtectionPolicy;
use App\Factories\AlbumFactory;
use App\Http\RuleSets\Album\SetAlbumProtectionPolicyRuleSet;
use App\Http\RuleSets\ChangeLoginRuleSet;
use App\Models\AccessPermission;
use App\Models\Extensions\BaseAlbum;
use App\Models\User;
use App\Policies\AlbumPolicy;
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
	public bool $is_nsfw = false; // ! wired
	public ?string $password = null; // ! wired
	public string $albumID;

	public function mount(BaseAlbum $album): void
	{
		$this->albumID = $album->id;
		$this->is_nsfw = $album->is_nsfw;

		/** @var AccessPermission $perm */
		$perm = $album->public_permissions();

		$this->is_public = $perm !== null;
		if ($this->is_public) {
			$this->setPublic($perm);
		}
	}

	private function setPublic(AccessPermission $perm): void
	{
		$this->grants_full_photo_access = $perm->grants_full_photo_access;
		$this->is_link_required = $perm->is_link_required;
		$this->grants_download = $perm->grants_download;
		$this->is_password_required = $perm->password !== null;
		// ! We do NOT load the password as we do not want to expose it.
	}

	private function setPrivate(): void
	{
		$this->grants_full_photo_access = false;
		$this->is_link_required = false;
		$this->is_password_required = false;
		$this->grants_download = false;
		$this->password = null;
		$this->is_password_required = false;
	}

	/**
	 * Simply render the form.
	 *
	 * @return View
	 */
	public function render(): View
	{
		return view('livewire.forms.album.visibility');
	}

	public function updated()
	{
		/** @var AlbumFactory $albumFactory */
		$albumFactory = resolve(AlbumFactory::class);
		$baseAlbum = $albumFactory->findBaseAlbumOrFail($this->albumID, false);

		$this->authorize(AlbumPolicy::CAN_EDIT, $baseAlbum);

		$this->validate(SetAlbumProtectionPolicyRuleSet::rules());

		if (!$this->is_public) {
			$this->setPrivate();
		}

		$albumProtectionPolicy = new AlbumProtectionPolicy(
			is_public: $this->is_public,
			is_link_required: $this->is_link_required,
			is_nsfw: $this->is_nsfw,
			grants_full_photo_access: $this->grants_full_photo_access,
			grants_download: $this->grants_download,
		);

		if (!$this->is_password_required) {
			$this->password = null;
		}
		if ($this->is_password_required && $this->password === '') {
			$this->password = null;
		}

		$setProtectionPolicy = resolve(SetProtectionPolicy::class);
		$setProtectionPolicy->do(
			$baseAlbum,
			$albumProtectionPolicy,
			!$this->is_password_required || ($this->is_password_required && $this->password !== null),
			$this->password
		);
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
