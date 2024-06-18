<?php

declare(strict_types=1);

namespace App\Livewire\Components\Modules\Maintenance;

use App\Livewire\Traits\Notify;
use App\Models\Configs;
use App\Models\SizeVariant;
use App\Policies\SettingsPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Livewire\Component;

/**
 * We may be missing some file sizes because of generation problems,
 * transfer of files, or other.
 * This module aims to solve this issue.
 */
class MissingFileSizes extends Component
{
	use Notify;

	/**
	 * Mount depending of the path.
	 *
	 * @return void
	 */
	public function mount(): void
	{
		Gate::authorize(SettingsPolicy::CAN_UPDATE, Configs::class);
	}

	/**
	 * Rendering of the front-end.
	 *
	 * @return View
	 */
	public function render(): View
	{
		Gate::authorize(SettingsPolicy::CAN_UPDATE, Configs::class);

		return view('livewire.modules.maintenance.fill-filesize-sizevariants');
	}

	/**
	 * Fetch the file size of existing size variants when the data is not in the DB
	 * Process by chunks of 500.
	 *
	 * @return void
	 */
	public function do(): void
	{
		Gate::authorize(SettingsPolicy::CAN_UPDATE, Configs::class);

		$variants_query = SizeVariant::query()
			->where('filesize', '=', 0)
			// TODO: remove s3 support here.
			->orderBy('id');
		// Internally, only holds $limit entries at once
		$variants = $variants_query->lazyById(500);

		$generated = 0;

		foreach ($variants as $variant) {
			$variantFile = $variant->getFile();
			if ($variantFile->exists()) {
				$variant->filesize = $variantFile->getFilesize();
				if (!$variant->save()) {
					Log::error('Failed to update filesize for ' . $variantFile->getRelativePath() . '.');
				} else {
					$generated++;
				}
			} else {
				Log::error('No file found at ' . $variantFile->getRelativePath() . '.');
			}
		}

		$this->notify(sprintf(__('maintenance.fill-filesize-sizevariants.success'), $generated));
	}

	/**
	 * Check how many images needs to be created.
	 *
	 * @return int
	 */
	public function getNumberOfMissingSizeProperty(): int
	{
		return SizeVariant::query()
			->where('filesize', '=', 0)
			// TODO: remove s3 support here.
			->count();
	}
}
