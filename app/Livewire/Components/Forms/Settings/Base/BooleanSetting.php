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
 * Basic boolean toggle.
 * No confirmation is requested.
 */
class BooleanSetting extends Component
{
	use Notify;

	public Configs $config;
	public string $description;
	public string $footer;
	public bool $flag; // ! Wired

	/**
	 * Mount the toggle.
	 *
	 * @param string $description - LANG key
	 * @param string $name        - Name of the config attribute
	 * @param string $footer      - text under the toggle if necessary
	 *
	 * @return void
	 */
	public function mount(string $description, string $name, string $footer = ''): void
	{
		$this->description = __('lychee.' . $description);
		$this->footer = $footer !== '' ? __('lychee.' . $footer) : '';
		$this->config = Configs::where('key', '=', $name)->firstOrFail();
	}

	/**
	 * Render the toggle element.
	 *
	 * @return View
	 */
	public function render(): View
	{
		$this->flag = $this->config->value === '1';

		return view('livewire.forms.settings.toggle');
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
	public function updating($field, $value)
	{
		Gate::check(SettingsPolicy::CAN_EDIT, [Configs::class]);

		$error_msg = $this->config->sanity($this->value);
		if ($error_msg === '') {
			$this->notify($error_msg, NotificationType::ERROR);

			return;
		}

		$this->config->value = $value === true ? '1' : '0';
		$this->config->save();
		$this->notify(__('lychee.CHANGE_SUCCESS'));
	}
}