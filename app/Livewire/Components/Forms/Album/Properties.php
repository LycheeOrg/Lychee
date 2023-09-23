<?php

namespace App\Livewire\Components\Forms\Album;

use App\Contracts\Http\Requests\RequestAttribute;
use App\DTO\PhotoSortingCriterion;
use App\Enum\ColumnSortingPhotoType;
use App\Enum\LicenseType;
use App\Enum\OrderSortingType;
use App\Factories\AlbumFactory;
use App\Http\RuleSets\Album\SetAlbumDescriptionRuleSet;
use App\Http\RuleSets\Album\SetAlbumSortingRuleSet;
use App\Livewire\Traits\Notify;
use App\Livewire\Traits\UseValidator;
use App\Models\Album as ModelsAlbum;
use App\Models\Extensions\BaseAlbum;
use App\Policies\AlbumPolicy;
use App\Rules\TitleRule;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rules\Enum;
use Livewire\Attributes\Locked;
use Livewire\Component;

class Properties extends Component
{
	use AuthorizesRequests;
	use UseValidator;
	use Notify;

	#[Locked] public string $albumID;
	public string $title; // ! wired
	public string $description; // ! wired
	public string $sorting_column = ''; // ! wired
	public string $sorting_order = ''; // ! wired
	public string $license = 'none';

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
		if ($album instanceof ModelsAlbum) {
			$this->license = $album->license->value;
		}
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
		$rules = [
			RequestAttribute::TITLE_ATTRIBUTE => ['required', new TitleRule()],
			RequestAttribute::LICENSE_ATTRIBUTE => ['required', new Enum(LicenseType::class)],
			...SetAlbumDescriptionRuleSet::rules(),
			...SetAlbumSortingRuleSet::rules(),
		];

		if (!$this->areValid($rules)) {
			return;
		}

		$baseAlbum = $albumFactory->findBaseAlbumOrFail($this->albumID, false);
		Gate::authorize(AlbumPolicy::CAN_EDIT, $baseAlbum);

		$baseAlbum->title = $this->title;
		$baseAlbum->description = $this->description;

		// Not super pretty but whatever.
		$column = ColumnSortingPhotoType::tryFrom($this->sorting_column);
		$order = OrderSortingType::tryFrom($this->sorting_order);
		$sortingCriterion = $column === null ? null : new PhotoSortingCriterion($column->toColumnSortingType(), $order);

		$baseAlbum->sorting = $sortingCriterion;

		if ($baseAlbum instanceof ModelsAlbum) {
			$baseAlbum->license = LicenseType::from($this->license);
		}

		$this->notify(__('lychee.CHANGE_SUCCESS'));
		$baseAlbum->save();
	}

	/**
	 * Return computed property so that it does not stay in memory.
	 *
	 * @return array column sorting
	 */
	final public function getPhotoSortingColumnsProperty(): array
	{
		// ? Dark magic: The ... will expand the array.
		return ['' => '-', ...ColumnSortingPhotoType::localized()];
	}

	/**
	 * Return computed property so that it does not stay in memory.
	 *
	 * @return array order
	 */
	final public function getSortingOrdersProperty(): array
	{
		// ? Dark magic: The ... will expand the array.
		return ['' => '-', ...OrderSortingType::localized()];
	}

	final public function getLicensesProperty(): array
	{
		return LicenseType::localized();
	}
}
