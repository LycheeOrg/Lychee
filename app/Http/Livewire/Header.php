<?php

namespace App\Http\Livewire;

use App\Models\Configs;
use Illuminate\Support\Facades\Config;
use Livewire\Component;

class Header extends Component
{
	/**
	 * @var string
	 */
	public $title = '';

	/**
	 * @var string
	 */
	public $mode;

	public function mount(?string $mode = 'albums', $album = null)
	{
		$this->title = Configs::get_value('site_title', Config::get('defines.defaults.SITE_TITLE'));
		if ($album != null && !is_array($album)) {
			$this->title = $album->title;
		}
		$this->mode = $mode ?? 'albums';
	}

	public function render()
	{
		return view('livewire.header');
	}
}

