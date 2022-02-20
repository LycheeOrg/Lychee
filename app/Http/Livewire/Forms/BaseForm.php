<?php

namespace App\Http\Livewire\Forms;

use App\Http\Livewire\Traits\InteractWithModal;
use Livewire\Component;

abstract class BaseForm extends Component
{
	use InteractWithModal;

	public string $title = '';
	public string $validate = '';
	public string $cancel = '';
	public array $params = [];

	abstract public function submit();

	public function mount(array $params = [])
	{
		$this->params = $params;
	}

	public function render()
	{
		return view('livewire.form.form');
	}

	public function close()
	{
		$this->closeModal();
	}
}
