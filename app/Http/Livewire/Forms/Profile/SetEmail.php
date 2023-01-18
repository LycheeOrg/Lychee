<?php

namespace App\Http\Livewire\Forms\Profile;

use App\Exceptions\UnauthenticatedException;
use App\Facades\Lang;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class SetEmail extends Component
{
	public string $description;
	public string $placeholder = 'email@example.com';
	public ?string $value; // ! Wired
	public string $action;

	public function mount() {
		$this->description = Lang::get('ENTER_EMAIL');
		$this->action = Lang::get('SAVE');
	}

	public function render()
	{
		$user = Auth::user() ?? throw new UnauthenticatedException();
		$this->value = $user->email;
		return view('livewire.forms.form-input');
	}

	public function save()
	{
		$user = Auth::user() ?? throw new UnauthenticatedException();
		$user->email = $this->value;
		$user->save();
	}
}