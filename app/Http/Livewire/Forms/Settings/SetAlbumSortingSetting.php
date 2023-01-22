<?php

namespace App\Http\Livewire\Forms\Settings;

use App\Enum\ColumnSortingAlbumType;
use App\Enum\OrderSortingType;
use App\Facades\Lang;
use App\Http\Livewire\Forms\Settings\Base\BaseConfigDoubleDropDown;
use App\Models\Configs;

class SetAlbumSortingSetting extends BaseConfigDoubleDropDown
{
	public function mount()
	{
		// We cannot abuse the sprintf in the case of blade templates compared to JS
		// So we do a simple preg_match to retrieve the chunks.
		// Note this assumes that %1$s is before %2$s !
		preg_match('/^(.*)%1\$s(.*)%2\$s(.*)$/', Lang::get('SORT_ALBUM_BY'), $matches);
		$this->begin = $matches[1];
		$this->middle = $matches[2];
		$this->end = $matches[3];

		$this->config1 = Configs::where('key', '=', 'sorting_albums_col')->firstOrFail();
		$this->config2 = Configs::where('key', '=', 'sorting_albums_order')->firstOrFail();
	}

	public function getOptions1Property(): array
	{
		return [
			ColumnSortingAlbumType::CREATED_AT->value => Lang::get('SORT_ALBUM_SELECT_1'),
			ColumnSortingAlbumType::TITLE->value => Lang::get('SORT_ALBUM_SELECT_2'),
			ColumnSortingAlbumType::DESCRIPTION->value => Lang::get('SORT_ALBUM_SELECT_3'),
			ColumnSortingAlbumType::IS_PUBLIC->value => Lang::get('SORT_ALBUM_SELECT_4'),
			ColumnSortingAlbumType::MAX_TAKEN_AT->value => Lang::get('SORT_ALBUM_SELECT_5'),
			ColumnSortingAlbumType::MIN_TAKEN_AT->value => Lang::get('SORT_ALBUM_SELECT_6'),
		];
	}

	public function getOptions2Property(): array
	{
		return [
			OrderSortingType::ASC->value => Lang::get('SORT_ASCENDING'),
			OrderSortingType::DESC->value => Lang::get('SORT_DESCENDING'),
		];
	}
}