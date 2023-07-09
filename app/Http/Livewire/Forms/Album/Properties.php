<?php

namespace App\Http\Livewire\Forms\Album;

use App\Models\Extensions\BaseAlbum;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Component;

class Properties extends Component
{
	use AuthorizesRequests;

	public string $title; // ! wired
	public string $description; // ! wired
	public string $album_id;
	public string $sort_by = ''; // ! wired
	public string $order_by = ''; // ! wired

	public function mount(BaseAlbum $album): void
	{
		$this->album_id = $album->id;
		$this->title = $album->title;
		$this->description = $album->description ?? '';
		$this->sort_by = $album->sorting?->column->value ?? '';
		$this->sort_by = $album->sorting?->order->value ?? '';
	}

	/**
	 * Simply render the form.
	 *
	 * @return View
	 */
	public function render(): View
	{
		return view('livewire.forms.album.properties');
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
