<?php

namespace App\Http\Livewire\Components\Base;

use Illuminate\View\View;

/**
 * Modal component, extends Openable.
 *
 * This aims to encapsulate any floating box that appears in Lychee Interface:
 * - login
 * - privacy properties...
 */
class ContextMenu extends Openable
{
	/**
	 * @var string defines the type of menu loaded in the pop-up.
	 *             It needs to be in App\Http\Livewire\ContextMenus.
	 */
	public string $type;

	/**
	 * @var array defines the arguments to be passed to the
	 *            Livewire component loaded inside the ContextMenu
	 */
	public array $params = [];

	/**
	 * This defined the events that the Component will intercept.
	 * In order to facilitate the use of those events, the trait
	 * app/Livewire/Traits/InteractWithModal.php can be used to
	 * add access to the modal.
	 *
	 * @var string[] listeners for modal events
	 * */
	protected $listeners = [
		'openContextMenu',
		'closeContextMenu',
		'deleteContextMenu',
	];

	/**
	 * Open the Context Menu.
	 *
	 * @param string $type   defines the Component loaded inside the modal
	 * @param array  $params Arguments to pass to the modal
	 *
	 * @return void
	 */
	public function openContextMenu(string $type, array $params = []): void
	{
		$this->type = $type;
		$this->params = $params;
		$this->open();
	}

	/**
	 * Close the ContextMenu component.
	 *
	 * @return void
	 */
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
