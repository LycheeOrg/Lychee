<?php

namespace App\Livewire\Components\Forms\Settings\Base;

use App\Enum\Livewire\NotificationType;
use App\Livewire\Traits\Notify;
use App\Models\Configs;
use App\Policies\SettingsPolicy;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\InvalidCastException;
use Illuminate\Database\Eloquent\JsonEncodingException;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;

/**
 * Basic double drop down menu for sortings.
 * Do note that it will save and update the value immediately.
 * No confirmation is requested.
 */
abstract class BaseConfigDoubleDropDown extends Component
{
	use Notify;

	public string $begin;
	public string $middle;
	public string $end;

	public Configs $config1;
	public Configs $config2;
	public string $value1; // ! Wired
	public string $value2; // ! Wired

	/**
	 * Renders the double drop down.
	 * Note that the values are synchroized with the two config attributes.
	 *
	 * @return View
	 */
	public function render(): View
	{
		$this->value1 = $this->config1->value;
		$this->value2 = $this->config2->value;

		return view('livewire.forms.settings.double-drop-down');
	}

	/**
	 * This runs before a wired property is updated.
	 *
	 * @param mixed $field
	 * @param mixed $value
	 *
	 * @return void
	 *
	 * @throws InvalidCastException
	 * @throws JsonEncodingException
	 * @throws \RuntimeException
	 */
	public function updated($field, $value)
	{
		Gate::check(SettingsPolicy::CAN_EDIT, [Configs::class]);
		$error_msg = $this->config1->sanity($this->value1);
		if ($error_msg !== '') {
			$this->notify($error_msg, NotificationType::ERROR);

			return;
		}
		$error_msg = $this->config2->sanity($this->value2);
		if ($error_msg !== '') {
			$this->notify($error_msg, NotificationType::ERROR);

			return;
		}

		$this->config1->value = $this->value1;
		$this->config1->save();
		$this->config2->value = $this->value2;
		$this->config2->save();
		$this->notify(__('lychee.CHANGE_SUCCESS'));
	}

	/**
	 * Defines accessor for the drop down options1.
	 *
	 * @return array
	 */
	abstract public function getOptions1Property(): array;

	/**
	 * Defines accessor for the drop down options2.
	 *
	 * @return array
	 */
	abstract public function getOptions2Property(): array;
}