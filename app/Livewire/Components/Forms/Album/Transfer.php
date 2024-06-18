<?php

declare(strict_types=1);

namespace App\Livewire\Components\Forms\Album;

use App\Contracts\Models\AbstractAlbum;
use App\Factories\AlbumFactory;
use App\Livewire\Traits\Notify;
use App\Livewire\Traits\UseValidator;
use App\Models\Album;
use App\Models\Extensions\BaseAlbum;
use App\Models\User;
use App\Policies\AlbumPolicy;
use App\Rules\AlbumIDRule;
use App\Rules\UsernameRule;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Locked;
use Livewire\Component;

class Transfer extends Component
{
	use AuthorizesRequests;
	use UseValidator;
	use Notify;

	#[Locked] public string $albumID;
	#[Locked] public string $title;
	#[Locked] public int $current_owner;
	public string $username; // ! wired

	/**
	 * This is the equivalent of the constructor for Livewire Components.
	 *
	 * @param BaseAlbum $album to update the attributes of
	 *
	 * @return void
	 */
	public function mount(BaseAlbum $album): void
	{
		Gate::authorize(AlbumPolicy::IS_OWNER, [AbstractAlbum::class, $album]);

		$this->albumID = $album->id;
		$this->title = $album->title;
		$this->current_owner = $album->owner_id;
		$this->username = $this->getUsersProperty()[0];
	}

	/**
	 * Simply render the form.
	 *
	 * @return View
	 */
	public function render(): View
	{
		return view('livewire.forms.album.transfer');
	}

	/**
	 * Return a table with the user_id and associated username.
	 *
	 * @return string[] list of usernames
	 */
	public function getUsersProperty(): array
	{
		return User::query()
			->select('username')
			->where('id', '<>', $this->current_owner)
			->orderBy('id', 'ASC')
			->pluck('username')
			->all();
	}

	/**
	 * Execute transfer of ownership.
	 *
	 * @param AlbumFactory $albumFactory
	 *
	 * @return RedirectResponse|View
	 */
	public function transfer(AlbumFactory $albumFactory)
	{
		$this->areValid([
			'albumID' => ['required', new AlbumIDRule(false)],
			'username' => ['required', new UsernameRule()],
		]);

		$baseAlbum = $albumFactory->findBaseAlbumOrFail($this->albumID, false);

		// We use CAN DELETE because it is pretty much the same. Only the owner and admin can transfer ownership
		Gate::authorize(AlbumPolicy::CAN_DELETE, $baseAlbum);

		$userId = User::query()
			->select(['id'])
			->where('username', '=', $this->username)
			->firstOrFail(['id'])->id;

		$baseAlbum->owner_id = $userId;
		$baseAlbum->save();

		// If this is an Album, we also need to fix the children and photos ownership
		if ($baseAlbum instanceof Album) {
			$baseAlbum->makeRoot();
			$baseAlbum->save();
			$baseAlbum->fixOwnershipOfChildren();
		}

		// If we are not an administrator, this mean we no longer have access.
		if (Auth::user()->may_administrate !== true) {
			return redirect()->to(route('livewire-gallery'));
		}

		// Remount the component and re-render.
		$this->mount($baseAlbum);
		$this->notify('Transfer successful!');

		return $this->render();
	}
}
