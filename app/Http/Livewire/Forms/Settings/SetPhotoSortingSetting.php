<?php

namespace App\Http\Livewire\Forms\Settings;

use App\Facades\Lang;
use App\Models\Configs;
use Livewire\Component;

class SetPhotoSortingSetting extends Component
{
	public string $begin;
	public string $middle;
	public string $end;

	public Configs $config1;
	public Configs $config2;
	public string $value1; // ! Wired
	public string $value2; // ! Wired

	public function mount()
	{
		// We cannot abuse the sprintf in the case of blade templates compared to JS
		// So we do a simple preg_match to retrieve the chunks.
		// Note this assumes that %1$s is before %2$s !
		preg_match('/^(.*)%1\$s(.*)%2\$s(.*)$/', Lang::get('SORT_PHOTO_BY'), $matches);
		$this->begin = $matches[1];
		$this->middle = $matches[2];
		$this->end = $matches[3];

		$this->config1 = Configs::where('key', '=', 'sorting_photos_col')->firstOrFail();
		$this->config2 = Configs::where('key', '=', 'sorting_photos_order')->firstOrFail();
	}

	public function render()
	{
		$this->value1 = $this->config1->value;
		$this->value2 = $this->config2->value;

		return view('livewire.form.form-double-drop-down');
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
		$this->config1->value = $this->value1;
		$this->config1->save();
		$this->config2->value = $this->value2;
		$this->config2->save();
	}

	public function getOptions1Property(): array
	{
		return [
			'created_at' => Lang::get('SORT_PHOTO_SELECT_1'),
			'taken_at' => Lang::get('SORT_PHOTO_SELECT_2'),
			'title' => Lang::get('SORT_PHOTO_SELECT_3'),
			'description' => Lang::get('SORT_PHOTO_SELECT_4'),
			'is_public' => Lang::get('SORT_PHOTO_SELECT_5'),
			'is_starred' => Lang::get('SORT_PHOTO_SELECT_6'),
			'type' => Lang::get('SORT_PHOTO_SELECT_7'),
		];
	}

	public function getOptions2Property(): array
	{
		return [
			'ASC' => Lang::get('SORT_ASCENDING'),
			'DESC' => Lang::get('SORT_DESCENDING'),
		];
	}
}