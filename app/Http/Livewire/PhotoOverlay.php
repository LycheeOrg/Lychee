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
	public $overlay = '';
	public $idx = 0;
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

		switch ($this->checkOverlayType()) {
			case 'desc':
				$this->overlay = $this->photo_data['description'];
				break;
			case 'date':
				if ($this->photo_data['takedate'] !== '') {
					$this->overlay = '<a>';
					$this->overlay .= "<span title='Camera Date'>";
					$this->overlay .= "<a class='badge camera-slr'>";
					$this->overlay .= "<svg class='iconic'><use xlink:href='#camera-slr' />";
					$this->overlay .= '</svg></a></span>';
					$this->overlay .= $this->photo_data['takedate'];
					$this->overlay .= '</a>';
				} else {
					$this->overlay = $this->data['sysdate'];
				}
				break;
			case 'exif':
				if ($this->genExifHash() !== '') {
					if ($this->photo_data['shutter'] !== '') {
						$this->overlay = str_replace('s', 'sec', $this->photo_data['shutter']);
					}
					if ($this->photo_data['aperture'] !== '') {
						if ($this->overlay !== '') {
							$this->overlay .= ' at ';
						}
						$this->overlay .= str_replace('f/', '&fnof; / ', $this->photo_data['aperture']);
					}
					if ($this->photo_data['iso'] !== '') {
						if ($this->overlay !== '') {
							$this->overlay .= ', ';
						}
						$this->overlay .= Lang::get('PHOTO_ISO') . ' ' . $this->photo_data['iso'];
					}
					if ($this->photo_data['focal'] !== '') {
						if ($this->overlay !== '') {
							$this->overlay .= '<br />';
						}
						$this->overlay .= $this->photo_data['focal'];
						if ($this->photo_data['lens'] !== '') {
							$this->overlay .= ' (' . $this->photo_data['focal'] . ')';
						}
					}
				}
				break;
			case 'none':
			default:
			;
		}

		if ($this->overlay !== '') {
			$this->overlay = '<p>' . $this->overlay . '</p>';
		}

		return view('livewire.photo-overlay');
	}
}
