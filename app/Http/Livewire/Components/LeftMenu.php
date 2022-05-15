<?php

namespace App\Http\Livewire\Components;

use App\Facades\AccessControl;
use App\Http\Livewire\Components\Base\Openable;
use Illuminate\View\View;
use Livewire\Redirector;

class LeftMenu extends Openable
{
	public function logout(): Redirector
	{
		AccessControl::logout();

		return redirect('/livewire/');
	}

	public function render(): View
	{
		return view('livewire.left-menu');
	}
}
