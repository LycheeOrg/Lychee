<?php

namespace App\Http\Livewire\Components\Base;

use Barryvdh\Debugbar\Facades\Debugbar;
use Livewire\Component;

/**
 * We define here Openable components.
 * This is a super class for:
 * - left menu
 * - the side menu
 * - the modals.
 */
class Openable extends Component
{
	/** @var bool status flag which defines whether the component is open or not. */
	public bool $isOpen = false;

	/** @var string[] Associative array. Those defines the events for which Openable is listening to. */
	protected $listeners = [
		'open',
		'close',
		'toggle',
	];

	/**
	 * Open the Component.
	 *
	 * @return void
	 */
	public function open(): void
	{
		Debugbar::info('request to open.');
		$this->isOpen = true;
	}

	/**
	 * Close the component.
	 *
	 * @return void
	 */
	public function close(): void
	{
		Debugbar::info('request to close.');
		$this->isOpen = false;
	}

	/**
	 * Toggle the component.
	 *
	 * @return void
	 */
	public function toggle(): void
	{
		Debugbar::info('toggle.');
		$this->isOpen = !$this->isOpen;
	}
}
