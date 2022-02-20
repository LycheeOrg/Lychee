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
			'username' => 'required|string',
			'password' => 'required|string',
		];
	}

	public function mount(array $params = [])
	{
		parent::mount($params);

		$this->title = 'Please login';
	}

	public function submit()
	{
		$data = $this->validate()['form'];

		// this is probably sensitive to timing attacks...
		if (AccessControl::log_as_admin($data['username'], $data['password'], request()->ip()) === true) {
			return response()->noContent();
		}

		if (AccessControl::log_as_user($data['username'], $data['password'], request()->ip()) === true) {
			return response()->noContent();
		}

		Logs::error(__METHOD__, __LINE__, 'User (' . $data['username'] . ') has tried to log in from ' . request()->ip());
	}
}
