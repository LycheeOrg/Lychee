<?php

namespace App\Livewire\Components\Forms\Settings\Base;

use App\Enum\Livewire\NotificationType;
use App\Livewire\Traits\Notify;
use App\Models\Configs;
use App\Policies\SettingsPolicy;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;

/**
 * Basic drop down menu.
 * Do note that it will save and update the value immediately.
 * No confirmation is requested.
 */
abstract class BaseConfigDropDown extends Component
{
	use Notify;

	public Configs $config;
	public string $description;
	public string $value; // ! Wired

	/**
	 * Renders the view of the dropdown menu.
	 *
	 * @return View
	 */
	public function render(): View
	{
		$this->value = $this->config->value;
		return view('livewire.forms.settings.drop-down');
	}

	/**
	 * This runs before a wired property is updated.
	 *
	 * @param mixed $field
	 * @param mixed $value
	 *
	 * @return void
	 */
	public function updating($field, $value): void
	{
		Gate::check(SettingsPolicy::CAN_EDIT, [Configs::class]);
		$error_msg = $this->config->sanity($this->value);
		if ($error_msg === '') {
			$this->notify($error_msg, NotificationType::ERROR);
			return;
		}

		$this->config->value = $this->value;
		$this->config->save();
		$this->notify(__('lychee.CHANGE_SUCCESS'));
	}

	/**
	 * Defines accessor for the drop down options1.
	 *
	 * @return array
	 */
	abstract public function getOptionsProperty(): array;
}