<?php

namespace App\Livewire\Traits;

use Illuminate\Support\Facades\Validator as ValidatorFacade;
use Illuminate\Validation\Validator;

/**
 * Send a notification to the Front End.
 */
trait UseValidator
{
	/**
	 * Send message to front-end, it will be displayed in the top right of the window.
	 *
	 * @param array $rules to apply
	 *
	 * @return bool
	 */
	public function areValid(array $rules): bool
	{
		/** @var Validator $validator */
		$validator = ValidatorFacade::make($this->all(), $rules);

		if ($validator->fails()) {
			$msg = '';
			foreach ($validator->getMessageBag()->messages() as $value) {
				$msg .= ($msg !== '' ? '<br>' : '') . implode('<br>', $value);
			}
			$this->dispatch('notify', ['msg' => $msg, 'type' => 'error']);

			return false;
		}

		return true;
	}
}