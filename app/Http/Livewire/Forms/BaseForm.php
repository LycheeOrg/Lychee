<?php

namespace App\Http\Livewire\Forms;

use App\Http\Livewire\Traits\InteractWithModal;
use Illuminate\View\View;
use Livewire\Component;

abstract class BaseForm extends Component
{
	use InteractWithModal;

	public string $title = '';
	public string $validate = '';
	public string $cancel = '';
	public array $params = [];

	abstract public function submit(): void;

	public function mount(array $params = []): void
	{
		$this->params = $params;
	}

	public function render(): View
	{
		return view('livewire.form.form');
	}

	public function close(): void
	{
		$this->closeModal();
	}
}
