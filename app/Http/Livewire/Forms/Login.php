<?php

namespace App\Http\Livewire\Forms;

use App\Exceptions\Internal\QueryBuilderException;
use App\Models\Logs;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

/**
 * This defines the Login Form used in modals.
 */
class Login extends BaseForm
{
	/**
	 * The form has 2 fields: username and password, containing empty values.
	 *
	 * @var array
	 */
	public array $form = [
		'username' => '',
		'password' => '',
	];

	/**
	 * The form fields are mapped to their Lang info.
	 *
	 * @var array
	 */
	public array $formName = [
		'form.username' => 'USERNAME',
		'form.password' => 'PASSWORD',
	];

	/**
	 * This defines the set of validation rules to be applied on the input.
	 * It would be a good idea to unify (namely reuse) the rules from the JSON api.
	 *
	 * @return array
	 */
	protected function rules(): array
	{
		return [
			'form.username' => 'required|string',
			'form.password' => 'required|string',
		];
	}

	/**
	 * Mount the component.
	 *
	 * @param array $params
	 *
	 * @return void
	 */
	public function mount(array $params = []): void
	{
		parent::mount($params);

		// ! TO BE LOCALIZED
		$this->title = 'Please login';
	}

	/**
	 * Hook the submit button.
	 *
	 * @return void
	 *
	 * @throws \Throwable
	 * @throws ValidationException
	 * @throws BindingResolutionException
	 * @throws \InvalidArgumentException
	 * @throws QueryBuilderException
	 */
	public function submit(): void
	{
		/*
		 * Empty error bag
		 */
		$this->resetErrorBag();

		/*
		 * Call Livewire validation on the from
		 */
		$data = $this->validate()['form'];

		// apply login as admin and trigger a reload
		if (Auth::attempt(['username' => $data['username'], 'password' => $data['password']])) {
			Logs::notice(__METHOD__, __LINE__, 'User (' . $data['username'] . ') has logged in from ' . request()->ip());
			$this->emitTo('pages.fullpage', 'reloadPage');

			return;
		}

		// Wrong login: stay on the modal and update the rendering.
		$this->addError('wrongLogin', 'Wrong login or password.');
		Logs::error(__METHOD__, __LINE__, 'User (' . $data['username'] . ') has tried to log in from ' . request()->ip());
	}
}
