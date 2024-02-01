<?php

namespace App\View\Components;

use App\Exceptions\ConfigurationKeyMissingException;
use App\Models\Configs;
use Illuminate\View\Component;
use Illuminate\View\View;

/**
 * This is the bottom of the page.
 * We provides socials etc...
 */
class BackButtonHeader extends Component
{
	public bool $enabled;
	public string $label;
	public string $url;
	public string $class;

	/**
	 * Initialize the footer once for all.
	 *
	 * @throws ConfigurationKeyMissingException
	 */
	public function __construct(string $class)
	{
		$this->class = $class;
		$this->enabled = Configs::getValueAsBool('back_button_enabled');
		$this->label = Configs::getValueAsString('back_button_text');
		$this->url = Configs::getValueAsString('back_button_url');
	}

	/**
	 * Render component.
	 *
	 * @return View
	 */
	public function render(): View
	{
		return view('components.header.back-button');
	}
}
