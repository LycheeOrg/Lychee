<?php

declare(strict_types=1);

namespace App\Livewire\Traits;

use Livewire\Attributes\On;

/**
 * Implementation of the Openable contract.
 */
trait UseOpenable
{
	/** @var bool status flag which defines whether the component is open or not. */
	public bool $isOpen = false;

	/**
	 * Open the Component.
	 *
	 * @return void
	 */
	#[On('open')]
	public function open(): void
	{
		$this->isOpen = true;
	}

	/**
	 * Close the component.
	 *
	 * @return void
	 */
	#[On('close')]
	public function close(): void
	{
		$this->isOpen = false;
	}

	/**
	 * Toggle the component.
	 *
	 * @return void
	 */
	#[On('toggle')]
	public function toggle(): void
	{
		$this->isOpen = !$this->isOpen;
	}
}