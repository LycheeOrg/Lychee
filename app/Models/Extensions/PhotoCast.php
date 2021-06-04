<?php

namespace App\Models\Extensions;

use App\ModelFunctions\SymLinkFunctions;
use App\Models\Photo;
use Illuminate\Support\Facades\Storage;

trait PhotoCast
{
	public function toReturnArray(): array
	{
		return $this->toArray();
	}

	/**
	 * Given a Photo, returns the thumb version.
	 */
	public function toThumb(): Thumb
	{
		/* @var $symLinkFunctions ?SymLinkFunctions */
		$symLinkFunctions = resolve(SymLinkFunctions::class);

		$thumb = new Thumb($this->type, $this->id);
		// maybe refactor?
		$sym = $symLinkFunctions->find($this);
		if ($sym !== null) {
			$thumb->thumb = $sym->get(SizeVariant::VARIANT_THUMB);
			// default is '' so if thumb2x does not exist we just reply '' which is the behaviour we want
			$thumb->thumb2x = $sym->get(SizeVariant::VARIANT_THUMB2X);
		} else {
			$thumb->thumb = Storage::url(
				SizeVariant::VARIANT_2_PATH_PREFIX[SizeVariant::VARIANT_THUMB] . '/' . $this->thumb_filename
			);
			if ($this->thumb2x == '1') {
				$thumb->set_thumb2x();
			}
		}

		return $thumb;
	}
}
