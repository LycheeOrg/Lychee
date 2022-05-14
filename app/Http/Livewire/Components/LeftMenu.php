<?php

namespace App\Http\Livewire\Components;

use App\Facades\AccessControl;
use App\Http\Livewire\Components\Base\Openable;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class LeftMenu extends Openable
{
	public function logout(): RedirectResponse
	{
		AccessControl::logout();

		return redirect('/livewire/');
	}

	public function render(): View
	{
		return view('livewire.left-menu');
	}
}
