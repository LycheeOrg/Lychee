<?php

namespace App\Http\Livewire\Forms\Album;

use App\Actions\Album\SetProtectionPolicy;
use App\DTO\AlbumProtectionPolicy;
use App\Factories\AlbumFactory;
use App\Http\RuleSets\Album\SetAlbumProtectionPolicyRuleSet;
use App\Models\AccessPermission;
use App\Models\Extensions\BaseAlbum;
use App\Policies\AlbumPolicy;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
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

	/**
	 * This is the equivalent of the constructor for Livewire Components.
	 *
	 * @param BaseAlbum $album to update the visibility of
	 *
	 * @return void
	 */
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

	/**
	 * When we initialize, we also need to update the public attributes of the components.
	 *
	 * @param AccessPermission $perm
	 *
	 * @return void
	 */
	private function setPublic(AccessPermission $perm): void
	{
		$this->grants_full_photo_access = $perm->grants_full_photo_access;
		$this->is_link_required = $perm->is_link_required;
		$this->grants_download = $perm->grants_download;
		$this->is_password_required = $perm->password !== null;
		// ! We do NOT load the password as we do not want to expose it.
	}

	/**
	 * When we set to Private, we automatically reset the all the attributes to false.
	 * The AccessPermission object associated will be destroyed later, as such it is better to reset the data.
	 *
	 * @return void
	 */
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

	/**
	 * If any attributes are changed, we call this.
	 *
	 * @return void
	 */
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
}
