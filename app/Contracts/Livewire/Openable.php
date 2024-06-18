<?php

declare(strict_types=1);

namespace App\Contracts\Livewire;

/**
 * This defines components which have an openable state.
 *
 * @property bool $isOpen whether the Openable component is open or not.
 */
interface Openable
{
	/**
	 * Open the component.
	 *
	 * @return void
	 */
	public function open(): void;

	/**
	 * Close the component.
	 *
	 * @return void
	 */
	public function close(): void;

	/**
	 * Toggle the component.
	 *
	 * @return void
	 */
	public function toggle(): void;
}
