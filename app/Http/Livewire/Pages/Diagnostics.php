<?php

namespace App\Http\Livewire\Pages;

use App\Actions\Diagnostics\Configuration;
use App\Actions\Diagnostics\Errors;
use App\Actions\Diagnostics\Info;
use App\Enum\Livewire\PageMode;
use Illuminate\View\View;
use Livewire\Component;

class Diagnostics extends Component
{
	public PageMode $mode = PageMode::DIAGNOSTICS;
	public string $error_msg = 'You must have administrator rights to see this.';

	/**
	 * We use a computed property instead of attributes
	 * in order to avoid poluting the data sent to the user.
	 */
	public function getErrorsProperty(Errors $collectErrors)
	{
		return $collectErrors->get(config('app.skip_diagnostics_checks'));
	}

	/**
	 * We use a computed property instead of attributes
	 * in order to avoid poluting the data sent to the user.
	 */
	public function getInfosProperty(Info $collectInfo)
	{
		return $collectInfo->get();
	}

	/**
	 * We use a computed property instead of attributes
	 * in order to avoid poluting the data sent to the user.
	 */
	public function getConfigsProperty(Configuration $collectConfig)
	{
		return $collectConfig->get();
	}

	/**
	 * Rendering of the front-end.
	 *
	 * @return View
	 */
	public function render(): View
	{
		return view('livewire.pages.diagnostics');
	}
}
