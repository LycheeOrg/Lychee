<?php

namespace App\Http\Livewire\Components;

use Livewire\Component;

class Modal extends Component
{
	public bool $isOpen = false;
	public string $opacity = '0';
	public string $type = '';
	public array $params = [];
	public string $modalSize = 'md:max-w-xl';

	protected $listeners = [
		'showModal' => 'open',
		'closeModal' => 'close',
		'deleteModal' => 'delete',
	];

	public function open(string $type, array $params = [])
	{
		$this->isOpen = true;
		$this->type = $type;
		$this->params = $params;
		$this->opacity = '100';
	}

	public function delete($params, string $form = 'forms.base-delete-form')
	{
		return $this->open($form, $params);
	}

	public function close()
	{
		$this->isOpen = false;
		$this->opacity = '0';
	}

	public function render()
	{
		return view('livewire.modal');
	}
}
