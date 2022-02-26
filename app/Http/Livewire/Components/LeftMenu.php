<?php

namespace App\Http\Livewire\Components;

use App\Facades\AccessControl;

class LeftMenu extends Openable
{
	public function logout()
	{
		AccessControl::logout();

		return redirect('/livewire/');
	}

	public function render()
	{
		return view('livewire.left-menu');
	}
}
