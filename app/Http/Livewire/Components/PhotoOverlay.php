<?php

namespace App\Http\Livewire\Components;

use App\Enum\Livewire\PhotoOverlayMode;
use App\Models\Configs;
use App\Models\Photo as PhotoModel;
use Illuminate\View\View;
use Livewire\Component;

/**
 * This is the overlay in the Photo mode.
 */
class PhotoOverlay extends Component
{
	public PhotoOverlayMode $type;

	public string $title = '';
	public string $overlay = '';
	public string $description = '';
	public bool $camera_date = false;
	public string $date = '';
	public string $exif1 = '';
	public string $exif2 = '';

	/** @var PhotoModel */
	private PhotoModel $photo_data;

	/**
	 * Mount the photo model and initialize the Component.
	 *
	 * @param PhotoModel $photo
	 *
	 * @return void
	 */
	public function mount(PhotoModel $photo): void
	{
		$this->photo_data = $photo;
		$this->type = Configs::getValueAsEnum('image_overlay_type', PhotoOverlayMode::class);
	}

	/**
	 * Return the valid OverlayType (and iterate if necessary).
	 *
	 * @return PhotoOverlayMode
	 */
	private function getOverlayType(): PhotoOverlayMode
	{
		$type = $this->type;
		for ($i = 0; $i < PhotoOverlayMode::count(); $i++) {
			if ($type === PhotoOverlayMode::DATE || $type === PhotoOverlayMode::NONE) {
				return $type;
			}
			if ($type === PhotoOverlayMode::DESC && $this->photo_data->description !== '') {
				return $type;
			}
			if ($type === PhotoOverlayMode::EXIF && $this->genExifHash() !== '') {
				return $type;
			}
			$type = $type->next();
		}

		return $type;
	}

	/**
	 * Compute a simple hash of the Exif data.
	 *
	 * @return string
	 */
	private function genExifHash(): string
	{
		$exifHash = $this->photo_data->make;
		$exifHash .= $this->photo_data->model;
		$exifHash .= $this->photo_data->shutter;
		if ($this->photo_data->isVideo()) {
			$exifHash .= $this->photo_data->aperture;
			$exifHash .= $this->photo_data->focal;
		}
		$exifHash .= $this->photo_data->iso;

		return $exifHash;
	}

	/**
	 * Render the component.
	 *
	 * @return View
	 */
	public function render(): View
	{
		$this->type = $this->getOverlayType();
		$this->title = $this->photo_data->title;
		$this->description = $this->photo_data->description ?? '';
		if ($this->photo_data->taken_at !== null) {
			$this->camera_date = true;
			$this->date = $this->photo_data->taken_at;
		} else {
			$this->camera_date = false;
			$this->date = $this->photo_data->created_at;
		}

		$exif1 = '';
		$exif2 = '';
		if ($this->genExifHash() !== '') {
			if ($this->photo_data->shutter !== '') {
				$exif1 = str_replace('s', 'sec', $this->photo_data->shutter);
			}
			if ($this->photo_data->aperture !== '') {
				$this->c($exif1, ' at ', str_replace('f/', '&fnof; / ', $this->photo_data->aperture));
			}
			if ($this->photo_data->iso !== '') {
				$this->c($exif1, ', ', __('lychee.PHOTO_ISO') . ' ' . $this->photo_data->iso);
			}
			if ($this->photo_data->focal !== '') {
				$exif2 = $this->photo_data->focal . ($this->photo_data->lens !== '' ? ' (' . $this->photo_data->lens . ')' : '');
			}
		}
		$this->exif1 = trim($exif1);
		$this->exif2 = trim($exif2);

		return view('livewire.components.photo-overlay');
	}

	/**
	 * Concatenation function.
	 *
	 * @param string $in      input string
	 * @param string $glue    concatenation glue (used only if input string is not '')
	 * @param string $content content to be appended
	 *
	 * @return void
	 */
	private function c(string &$in, string $glue, string $content): void
	{
		if ($in !== '') {
			$in .= $glue;
		}
		$in .= $content;
	}
}
