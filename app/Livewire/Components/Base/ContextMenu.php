<?php

namespace App\Livewire\Components\Base;

use App\Contracts\Livewire\Openable;
use App\Livewire\Traits\UseOpenable;
use Illuminate\View\View;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Livewire\Component;

/**
 * Modal component, extends Component.
 *
 * This aims to encapsulate any floating box that appears in Lychee Interface:
 * - login
 * - privacy properties...
 */
class ContextMenu extends Component implements Openable
{
	use UseOpenable;

	/**
	 * @var string defines the type of menu loaded in the pop-up.
	 *             It needs to be in App\Http\Livewire\ContextMenus.
	 */
	public string $type;

	/**
	 * @var int defines the x position of the menu
	 */
	public int $x;

	/**
	 * @var int defines the y position of the menu
	 */
	public int $y;

	public int $transformXorigin;
	public int $transformYorigin;

	/**
	 * @var array defines the arguments to be passed to the
	 *            Livewire component loaded inside the ContextMenu
	 */
	#[Locked]
	public array $params = [];

	/**
	 * Open the Context Menu.
	 *
	 * @param string $type   defines the Component loaded inside the modal
	 * @param array  $params Arguments to pass to the modal
	 *
	 * @return void
	 */
	#[On('openContextMenu')]
	public function openContextMenu(string $type, int $x = 30, int $y = 30, array $params = [], $transformXorigin = 0, $transformYorigin = 0): void
	{
		$this->type = $type;
		$this->params = $params;
		$this->x = $x;
		$this->y = $y;
		$this->transformXorigin = $transformXorigin;
		$this->transformYorigin = $transformYorigin;
		$this->open();
	}

	/**
	 * Close the ContextMenu component.
	 *
	 * @return void
	 */
	#[On('closeContextMenu')]
	public function closeContextMenu(): void
	{
		$this->close();
	}

	/**
	 * Rendering of the Component.
	 *
	 * @return View
	 */
	public function render(): View
	{
		return view('livewire.components.context-menu');
	}
}
