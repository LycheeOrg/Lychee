<?php

namespace App\Livewire\Components\Modules\Maintenance;

use App\Contracts\Models\SizeVariantFactory;
use App\Enum\SizeVariantType;
use App\Image\SizeVariantDimensionHelpers;
use App\Livewire\Traits\Notify;
use App\Models\Configs;
use App\Models\Photo;
use App\Models\SizeVariant;
use App\Policies\SettingsPolicy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Livewire\Attributes\Locked;
use Livewire\Component;

/**
 * We may miss some size variants because of generation problem,
 * transfer of files, or other.
 * This module aims to solve this issue.
 */
class GenSizeVariants extends Component
{
	use Notify;

	#[Locked] public string $type = '';
	private SizeVariantDimensionHelpers $svHelpers;

	/**
	 * Initialize the helpers.
	 *
	 * @return void
	 */
	public function boot(): void
	{
		$this->svHelpers = new SizeVariantDimensionHelpers();
	}

	/**
	 * Mount depending of the path.
	 *
	 * @param string $type to generate for
	 *
	 * @return void
	 */
	public function mount(string $type): void
	{
		Gate::authorize(SettingsPolicy::CAN_UPDATE, Configs::class);

		$this->type = $type;
	}

	/**
	 * Rendering of the front-end.
	 *
	 * @return View
	 */
	public function render(): View
	{
		Gate::authorize(SettingsPolicy::CAN_UPDATE, Configs::class);

		return view('livewire.modules.maintenance.gen-sizevariants');
	}

	/**
	 * Generates missing size variants by chunk of 100.
	 *
	 * @return void
	 */
	public function do(): void
	{
		Gate::authorize(SettingsPolicy::CAN_UPDATE, Configs::class);

		$photos = Photo::query()
			->where('type', 'like', 'image/%')
			->with('size_variants')
			->whereDoesntHave('size_variants', function (Builder $query) {
				$query->where('type', '=', $this->getSvProperty());
			})
			->take(100)
			->get();

		// Initialize factory for size variants
		$sizeVariantFactory = resolve(SizeVariantFactory::class);

		$generated = 0;
		/** @var Photo $photo */
		foreach ($photos as $photo) {
			$sizeVariantFactory->init($photo);
			$sizeVariant = $sizeVariantFactory->createSizeVariantCond($this->getSvProperty());
			if ($sizeVariant !== null) {
				$generated++;
				Log::notice($this->type . ' (' . $sizeVariant->width . 'x' . $sizeVariant->height . ') for ' . $photo->title . ' created.');
			} else {
				Log::error('Did not create ' . $this->type . ' for ' . $photo->title . '.');
			}
		}

		$this->notify(sprintf(__('maintenance.gen-sizevariants.success'), $generated, $this->getSvProperty()->name()));
	}

	/**
	 * Check how many images needs to be created.
	 *
	 * @return int
	 */
	public function getNumberOfSizeVariantsToGenerateProperty(): int
	{
		if (!$this->svHelpers->isEnabledByConfiguration($this->getSvProperty())) {
			return 0;
		}

		$numGenerated = SizeVariant::query()->where('type', '=', $this->getSvProperty())->count();

		$totalToHave = SizeVariant::query()->where(fn ($q) => $q
				->when($this->svHelpers->getMaxWidth($this->getSvProperty()) !== 0, fn ($q1) => $q1->where('width', '>', $this->svHelpers->getMaxWidth($this->getSvProperty())))
				->when($this->svHelpers->getMaxHeight($this->getSvProperty()) !== 0, fn ($q2) => $q2->orWhere('height', '>', $this->svHelpers->getMaxHeight($this->getSvProperty())))
		)
		->where('type', '=', SizeVariantType::ORIGINAL)
		->count();

		return $totalToHave - $numGenerated;
	}

	/**
	 * Easy accessor for the Enum type.
	 *
	 * @return SizeVariantType
	 */
	public function getSvProperty(): SizeVariantType
	{
		return SizeVariantType::from($this->type);
	}
}
