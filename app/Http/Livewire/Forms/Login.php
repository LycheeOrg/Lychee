<?php

namespace App\Http\Livewire\Forms;

use App\Facades\AccessControl;
use App\Models\Logs;

class Login extends BaseForm
{
	public array $form = [
		'username' => '',
		'password' => '',
	];

	public array $formName = [
		'form.username' => 'USERNAME',
		'form.password' => 'PASSWORD',
	];

	protected function rules()
	{
		return [
			'form.username' => 'required|string',
			'form.password' => 'required|string',
		];
	}

	public function mount(array $params = [])
	{
		parent::mount($params);

		$this->title = 'Please login';
	}

	public function submit()
	{
		$this->resetErrorBag();

		$data = $this->validate()['form'];

		if (AccessControl::log_as_admin($data['username'], $data['password'], request()->ip()) === true) {
			return $this->emitTo('pages.fullpage', 'reloadPage');
		}
		if (AccessControl::log_as_user($data['username'], $data['password'], request()->ip()) === true) {
			return $this->emitTo('pages.fullpage', 'reloadPage');
		}

		$this->addError('wrongLogin', 'Wrong login or password.');
		Logs::error(__METHOD__, __LINE__, 'User (' . $data['username'] . ') has tried to log in from ' . request()->ip());
	}
}
