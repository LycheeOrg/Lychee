<?php

namespace App\Livewire\Components\Forms\Album;

use App\Contracts\Models\AbstractAlbum;
use App\DTO\AlbumSortingCriterion;
use App\DTO\PhotoSortingCriterion;
use App\Enum\AspectRatioType;
use App\Enum\ColumnSortingAlbumType;
use App\Enum\ColumnSortingPhotoType;
use App\Enum\LicenseType;
use App\Enum\OrderSortingType;
use App\Factories\AlbumFactory;
use App\Http\RuleSets\Album\SetAlbumDescriptionRuleSet;
use App\Http\RuleSets\Album\SetAlbumSortingRuleSet;
use App\Http\RuleSets\Album\SetPhotoSortingRuleSet;
use App\Legacy\V1\Contracts\Http\Requests\RequestAttribute;
use App\Livewire\Traits\Notify;
use App\Livewire\Traits\UseValidator;
use App\Models\Album as ModelsAlbum;
use App\Models\Extensions\BaseAlbum;
use App\Models\TagAlbum;
use App\Policies\AlbumPolicy;
use App\Rules\CopyrightRule;
use App\Rules\TitleRule;
use Illuminate\Contracts\Container\BindingResolutionException;
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
	#[Locked] public bool $is_model_album;
	#[Locked] public bool $is_tag_album;
	public string $title; // ! wired
	public string $description; // ! wired
	public string $photo_sorting_column = ''; // ! wired
	public string $photo_sorting_order = ''; // ! wired
	public string $album_sorting_column = ''; // ! wired
	public string $album_sorting_order = ''; // ! wired
	public string $album_aspect_ratio = ''; // ! wired
	public string $license = 'none'; // ! wired
	public string $copyright = ''; // ! wired
	public ?string $tag = ''; // ! wired

	/**
	 * This is the equivalent of the constructor for Livewire Components.
	 *
	 * @param BaseAlbum $album to update the attributes of
	 *
	 * @return void
	 */
	public function mount(BaseAlbum $album): void
	{
		Gate::authorize(AlbumPolicy::CAN_EDIT, [AbstractAlbum::class, $album]);

		$this->is_model_album = $album instanceof ModelsAlbum;
		$this->is_tag_album = $album instanceof TagAlbum;

		$this->albumID = $album->id;
		$this->title = $album->title;
		$this->description = $album->description ?? '';
		$this->photo_sorting_column = $album->photo_sorting?->column->value ?? '';
		$this->photo_sorting_order = $album->photo_sorting?->order->value ?? '';
		$this->copyright = $album->copyright ?? '';
		if ($this->is_model_album) {
			/** @var ModelsAlbum $album */
			$this->license = $album->license->value;
			$this->album_sorting_column = $album->album_sorting?->column->value ?? '';
			$this->album_sorting_order = $album->album_sorting?->order->value ?? '';
			$this->album_aspect_ratio = $album->album_thumb_aspect_ratio?->value ?? '';
		}
		if ($this->is_tag_album) {
			/** @var TagAlbum $album */
			$this->tag = implode(', ', $album->show_tags);
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
			...SetPhotoSortingRuleSet::rules(),
			...SetAlbumSortingRuleSet::rules(),
			RequestAttribute::ALBUM_ASPECT_RATIO_ATTRIBUTE => ['present', 'nullable', new Enum(AspectRatioType::class)],
			RequestAttribute::COPYRIGHT_ATTRIBUTE => ['present', 'nullable', new CopyrightRule()],
		];

		if (!$this->areValid($rules)) {
			return;
		}

		$baseAlbum = $albumFactory->findBaseAlbumOrFail($this->albumID, false);
		Gate::authorize(AlbumPolicy::CAN_EDIT, $baseAlbum);

		$baseAlbum->title = $this->title;
		$baseAlbum->description = $this->description;

		$this->copyright = trim($this->copyright);

		// Not super pretty but whatever.
		$column = ColumnSortingPhotoType::tryFrom($this->photo_sorting_column);
		$order = OrderSortingType::tryFrom($this->photo_sorting_order);
		$photoSortingCriterion = $column === null ? null : new PhotoSortingCriterion($column->toColumnSortingType(), $order);
		$baseAlbum->photo_sorting = $photoSortingCriterion;

		// If left empty, we set to null
		$baseAlbum->copyright = $this->copyright === '' ? null : $this->copyright;

		if ($this->is_model_album) {
			/** @var ModelsAlbum $baseAlbum */
			$baseAlbum->license = LicenseType::from($this->license);

			$column = ColumnSortingAlbumType::tryFrom($this->album_sorting_column);
			$order = OrderSortingType::tryFrom($this->album_sorting_order);
			$albumSortingCriterion = $column === null ? null : new AlbumSortingCriterion($column->toColumnSortingType(), $order);
			$baseAlbum->album_sorting = $albumSortingCriterion;
			$baseAlbum->album_thumb_aspect_ratio = AspectRatioType::tryFrom($this->album_aspect_ratio);
		}
		if ($this->is_tag_album) {
			/** @var TagAlbum $baseAlbum */
			$baseAlbum->show_tags = collect(explode(',', $this->tag))->map(fn ($v) => trim($v))->filter(fn ($v) => $v !== '')->all();
		}

		$this->notify(__('lychee.CHANGE_SUCCESS'));
		$baseAlbum->save();
	}

	/**
	 * Return computed property so that it does not stay in memory.
	 *
	 * @return array<string,string> column sorting
	 */
	final public function getPhotoSortingColumnsProperty(): array
	{
		// ? Dark magic: The ... will expand the array.
		return ['' => '-', ...ColumnSortingPhotoType::localized()];
	}

	/**
	 * Return computed property so that it does not stay in memory.
	 *
	 * @return array<string,string> column sorting
	 */
	final public function getAlbumSortingColumnsProperty(): array
	{
		// ? Dark magic: The ... will expand the array.
		return ['' => '-', ...ColumnSortingAlbumType::localized()];
	}

	/**
	 * Return computed property so that it does not stay in memory.
	 *
	 * @return array<string,string> order
	 */
	final public function getSortingOrdersProperty(): array
	{
		// ? Dark magic: The ... will expand the array.
		return ['' => '-', ...OrderSortingType::localized()];
	}

	/**
	 * Return computed property so that it does not stay in memory.
	 *
	 * @return array<string,string> order
	 */
	final public function getAspectRatiosProperty(): array
	{
		// ? Dark magic: The ... will expand the array.
		return ['' => '-', ...AspectRatioType::localized()];
	}

	/**
	 * Return the list of license localized.
	 *
	 * @return array<string,string>
	 *
	 * @throws BindingResolutionException
	 */
	final public function getLicensesProperty(): array
	{
		return LicenseType::localized();
	}
}
