<?php

namespace App\Http\Livewire;

use App\Locale\Lang;
use App\Models\Configs;
use Livewire\Component;

class Header extends Component
{
	public $locale;

	public function render()
	{
		$this->locale = Lang::get_lang(Configs::get_value('lang'));

		return view('livewire.header');
	}
}
