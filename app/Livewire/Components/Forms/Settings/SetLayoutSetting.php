<?php

namespace App\Livewire\Components\Forms\Settings;

use App\Enum\AlbumLayoutType;
use App\Enum\Livewire\NotificationType;
use App\Livewire\Components\Forms\Settings\Base\BaseConfigDropDown;
use App\Models\Configs;
use App\Policies\SettingsPolicy;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Gate;

/**
 * Defines the drop down menu for the layout used by the gallery.
 */
class SetLayoutSetting extends BaseConfigDropDown
{
	/**
	 * Provides the different options.
	 *
	 * @return array
	 */
	public function getOptionsProperty(): array
	{
		return AlbumLayoutType::localized();
	}

	/**
	 * Mount the texts.
	 *
	 * @return void
	 */
	public function mount()
	{
		$this->description = __('lychee.LAYOUT_TYPE');
		$this->config = Configs::where('key', '=', 'layout')->firstOrFail();
	}

	public function render(): View
	{
		$keys = array_keys($this->getOptionsProperty());
		$this->value = $keys[intval($this->config->value)];

		return view('livewire.forms.settings.drop-down');
	}

	public function updating($field, $value): void
	{
		Gate::authorize(SettingsPolicy::CAN_EDIT, [Configs::class]);

		// Fetch the keys and reverse index.
		$reverse = array_flip(array_keys($this->getOptionsProperty()));
		$value = strval($reverse[$value]);

		$error_msg = $this->config->sanity($value);
		if ($error_msg !== '') {
			$this->notify($error_msg, NotificationType::ERROR);

			return;
		}

		$this->config->value = $value;
		$this->config->save();
		$this->notify(__('lychee.CHANGE_SUCCESS'));
	}
}