<?php

declare(strict_types=1);

namespace App\Livewire\Components\Forms\Settings\Base;

use App\Enum\Livewire\NotificationType;
use App\Livewire\Traits\Notify;
use App\Models\Configs;
use App\Policies\SettingsPolicy;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Locked;
use Livewire\Component;

/**
 * String setting input.
 * To persist the data a call to save() is required.
 */
class StringSetting extends Component
{
	use Notify;

	public Configs $config;
	#[Locked] public string $description;
	#[Locked] public string $placeholder;
	#[Locked] public string $action;
	public string $value; // ! Wired

	/**
	 * @param string $description - LANG key
	 * @param string $name        - name of the config attribute
	 * @param string $placeholder - LANG key
	 * @param string $action      - LANG key
	 *
	 * @return void
	 */
	public function mount(string $description, string $name, string $placeholder, string $action): void
	{
		$this->description = __('lychee.' . $description);
		$this->action = __('lychee.' . $action);
		$this->placeholder = __('lychee.' . $placeholder);
		$this->config = Configs::where('key', '=', $name)->firstOrFail();
	}

	/**
	 * Renders the input form.
	 *
	 * @return View
	 */
	public function render(): View
	{
		$this->value = $this->config->value;

		return view('livewire.forms.settings.input');
	}

	/**
	 * Validation call to persist the data (as opposed to drop down menu and toggle which are instant).
	 *
	 * @return void
	 */
	public function save(): void
	{
		Gate::authorize(SettingsPolicy::CAN_EDIT, [Configs::class]);
		$error_msg = $this->config->sanity($this->value);
		if ($error_msg !== '') {
			$this->notify($error_msg, NotificationType::ERROR);
			$this->value = $this->config->value;

			return;
		}

		$this->config->value = $this->value;
		$this->config->save();
		$this->notify(__('lychee.CHANGE_SUCCESS'));
	}
}