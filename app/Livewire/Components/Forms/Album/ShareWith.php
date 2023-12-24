<?php

namespace App\Livewire\Components\Forms\Album;

use App\Contracts\Models\AbstractAlbum;
use App\Exceptions\Internal\QueryBuilderException;
use App\Exceptions\UnauthorizedException;
use App\Livewire\Traits\Notify;
use App\Livewire\Traits\UseValidator;
use App\Models\AccessPermission;
use App\Models\Configs;
use App\Models\Extensions\BaseAlbum;
use App\Models\User;
use App\Policies\AlbumPolicy;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Livewire\Component;

class ShareWith extends Component
{
	use AuthorizesRequests;
	use UseValidator;
	use Notify;

	public BaseAlbum $album;

	public array $perms;

	public ?string $search = null; // ! wired

	public ?int $userID = null;
	public ?string $username = null;

	public bool $grants_full_photo_access;
	public bool $grants_download;
	public bool $grants_upload;
	public bool $grants_edit;
	public bool $grants_delete;

	/**
	 * This is the equivalent of the constructor for Livewire Components.
	 *
	 * @param BaseAlbum $album to update the attributes of
	 *
	 * @return void
	 */
	public function mount(BaseAlbum $album): void
	{
		$this->album = $album;
		Gate::authorize(AlbumPolicy::CAN_SHARE_WITH_USERS, [AbstractAlbum::class, $this->album]);
		$this->resetData();
	}

	/**
	 * Simply render the form.
	 *
	 * @return View
	 */
	public function render(): View
	{
		return view('livewire.forms.album.share-with');
	}

	/**
	 * Return the list of users to share with.
	 * This is basically:
	 * - Not the current user.
	 * - Not the owner of the album (just to be sure)
	 * - Not the admins (they already have all access)
	 * - Not users which have already been shared.
	 *
	 * @return array
	 *
	 * @throws UnauthorizedException
	 * @throws QueryBuilderException
	 */
	public function getUserListProperty(): array
	{
		$alreadySelected = collect($this->perms)
			->map(fn (AccessPermission $perm) => $perm->user_id)
			->all();

		$id = Auth::id() ?? throw new UnauthorizedException();
		$filtered = User::query()
			->where('id', '<>', $id)
			->where('id', '<>', $this->album->owner_id)
			->where('may_administrate', '<>', true)
			->whereNotIn('id', $alreadySelected)
			->orderBy('username', 'ASC')
			->get()
			->map(fn (User $usr) => ['id' => $usr->id, 'username' => $usr->username]);

		if ($this->search !== null && trim($this->search) !== '') {
			return $filtered->filter(function (array $album) {
				return Str::contains($album['username'], ltrim($this->search), true);
			})->all();
		}

		return $filtered->all();
	}

	public function add(): void
	{
		Gate::authorize(AlbumPolicy::CAN_SHARE_WITH_USERS, [AbstractAlbum::class, $this->album]);

		$perm = new AccessPermission();
		$perm->user_id = $this->userID;
		$perm->base_album_id = $this->album->id;
		$perm->grants_full_photo_access = $this->grants_full_photo_access;
		$perm->grants_download = $this->grants_download;
		$perm->grants_upload = $this->grants_upload;
		$perm->grants_edit = $this->grants_edit;
		$perm->grants_delete = $this->grants_delete;
		$perm->save();

		$this->resetData();
	}

	public function select(int $userID, string $username): void
	{
		$this->userID = $userID;
		$this->username = $username;
	}

	public function clearUsername(): void
	{
		$this->userID = null;
		$this->username = null;
	}

	private function resetData(): void
	{
		$this->perms = $this->album->access_permissions()->with(['user', 'album'])->whereNotNull('user_id')->get()->all();
		$this->grants_download = Configs::getValueAsBool('grants_download');
		$this->grants_full_photo_access = Configs::getValueAsBool('grants_full_photo_access');
		$this->grants_upload = false;
		$this->grants_edit = false;
		$this->grants_delete = false;
		$this->search = null;
		$this->userID = null;
		$this->username = null;
	}

	public function delete(int $id): void
	{
		$perm = AccessPermission::with('album')->findOrFail($id);
		Gate::authorize(AlbumPolicy::CAN_SHARE_WITH_USERS, [AbstractAlbum::class, $perm->album]);

		AccessPermission::query()->where('id', '=', $id)->delete();
		$this->resetData();
	}
}
