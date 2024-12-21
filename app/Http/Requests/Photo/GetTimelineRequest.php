<?php

namespace App\Http\Requests\Photo;

use App\Http\Requests\AbstractEmptyRequest;
use App\Models\Configs;
use Illuminate\Support\Facades\Auth;

class GetTimelineRequest extends AbstractEmptyRequest
{
	/**
	 * {@inheritDoc}
	 */
	public function authorize(): bool
	{
		if (!Auth::check() && !Configs::getValueAsBool('timeline_photos_public')) {
			return false;
		}

		return Configs::getValueAsBool('timeline_photos_enabled');
	}
}