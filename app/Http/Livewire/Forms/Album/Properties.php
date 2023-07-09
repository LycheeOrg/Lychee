<?php

namespace App\Http\Livewire\Forms\Album;

use App\Contracts\Http\Requests\RequestAttribute;
use App\DTO\PhotoSortingCriterion;
use App\Enum\ColumnSortingPhotoType;
use App\Enum\OrderSortingType;
use App\Factories\AlbumFactory;
use App\Http\RuleSets\Album\SetAlbumDescriptionRuleSet;
use App\Http\RuleSets\Album\SetAlbumSortingRuleSet;
use App\Models\Extensions\BaseAlbum;
use App\Policies\AlbumPolicy;
use App\Rules\TitleRule;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Component;

class Properties extends Component
{
	use AuthorizesRequests;

	public string $title; // ! wired
	public string $description; // ! wired
	public string $albumID;
	public string $sorting_column = ''; // ! wired
	public string $sorting_order = ''; // ! wired

	public array $sorting_columns;
	public array $sorting_orders;

	/**
	 * This is the equivalent of the constructor for Livewire Components.
	 *
	 * @param BaseAlbum $album to update the attributes of
	 *
	 * @return void
	 */
	public function mount(BaseAlbum $album): void
	{
		$this->albumID = $album->id;
		$this->title = $album->title;
		$this->description = $album->description ?? '';
		$this->sorting_column = $album->sorting?->column->value ?? '';
		$this->sorting_order = $album->sorting?->order->value ?? '';

		// ? Dark magic: The ... will expand the array.
		$this->sorting_columns = ['' => '-', ...ColumnSortingPhotoType::toTranslation()];
		$this->sorting_orders = ['' => '-', ...OrderSortingType::toTranslation()];

		// SetAlbumSortingRuleSet::rules();
	}

	/**
	 * Simply render the form.
	 *
	 * @return View
	 */
	public function render(): View
	{
		return view('livewire.forms.album.properties');
	}

	/**
	 * Update Username & Password of current user.
	 */
	public function submit(AlbumFactory $albumFactory): void
	{
		$baseAlbum = $albumFactory->findBaseAlbumOrFail($this->albumID, false);

		$this->validate(SetAlbumSortingRuleSet::rules());
		$this->validate(SetAlbumDescriptionRuleSet::rules());
		$this->validate([RequestAttribute::TITLE_ATTRIBUTE => ['required', new TitleRule()]]);

		$this->authorize(AlbumPolicy::CAN_EDIT, $baseAlbum);

		$baseAlbum->title = $this->title;
		$baseAlbum->description = $this->description;

		// Not super pretty but whatever.
		$column = ColumnSortingPhotoType::tryFrom($this->sorting_column);
		$order = OrderSortingType::tryFrom($this->sorting_order);
		$sortingCriterion = $column === null ? null : new PhotoSortingCriterion($column->toColumnSortingType(), $order);

		$baseAlbum->sorting = $sortingCriterion;
		$baseAlbum->save();
	}
}
