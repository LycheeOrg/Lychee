<?php

namespace App\Http\Livewire;

use App\Models\Configs;
use Illuminate\Support\Str;
use Lang;
use Livewire\Component;

class PhotoOverlay extends Component
{
	private $types = ['desc', 'date', 'exif', 'none'];
	public $title = '';
	public $type = 'none';
	public $overlay = '';
	public $idx = 0;
	public $description = '';
	public $camera_date = false;
	public $date = '';
	public $exif1 = '';
	public $exif2 = '';

	private $photo_data;

	public function mount(array $data)
	{
		$this->photo_data = $data;
		$overlay_type = Configs::get_value('image_overlay_type', 'none');

		$this->idx = array_search($overlay_type, $this->types, true);
	}

	private function checkOverlayType(): string
	{
		if ($this->idx < 0) {
			return 'none';
		}

		$n = count($this->types);
		for ($i = 0; $i < $n; $i++) {
			$type = $this->types[($this->idx + $i) % $n];
			if ($type === 'date' || $type === 'none') {
				return $type;
			}
			if ($type === 'desc' && $this->photo_data['description'] !== '') {
				return $type;
			}
			if ($type === 'exif' && $this->genExifHash() !== '') {
				return $type;
			}
		}
	}

	private function genExifHash()
	{
		$exifHash = $this->photo_data['make'];
		$exifHash .= $this->photo_data['model'];
		$exifHash .= $this->photo_data['shutter'];
		if (Str::contains($this->photo_data['type'], 'video')) {
			$exifHash .= $this->photo_data['aperture'];
			$exifHash .= $this->photo_data['focal'];
		}
		$exifHash .= $this->photo_data['iso'];

		return $exifHash;
	}

	public function render()
	{
		$this->title = $this->photo_data['title'];

		$this->type = $this->checkOverlayType();
		$this->description = $this->photo_data['description'];
		if ($this->photo_data['takedate'] !== '') {
			$this->camera_date = true;
			$this->date = $this->photo_data['takedate'];
		} else {
			$this->camera_date = false;
			$this->date = $this->data['sysdate'];
		}

		$exif1 = '';
		$exif2 = '';
		if ($this->genExifHash() !== '') {
			if ($this->photo_data['shutter'] !== '') {
				$exif1 = str_replace('s', 'sec', $this->photo_data['shutter']);
			}
			if ($this->photo_data['aperture'] !== '') {
				$this->c($exif1, ' at ', str_replace('f/', '&fnof; / ', $this->photo_data['aperture']));
			}
			if ($this->photo_data['iso'] !== '') {
				$this->c($exif1, ', ', Lang::get('PHOTO_ISO') . ' ' . $this->photo_data['iso']);
			}
			if ($this->photo_data['focal'] !== '') {
				$exif2 = $this->photo_data['focal'] . ($this->photo_data['lens'] !== '' ? ' (' . $this->photo_data['lens'] . ')' : '');
			}
		}
		$this->exif1 = trim($exif1);
		$this->exif2 = trim($exif2);

		return view('livewire.photo-overlay');
	}

	private function c(string &$in, string $glue, string $content): void
	{
		if ($in !== '') {
			$in .= $glue;
		}
		$in .= $content;
	}
}
