<?php

namespace App\Actions\Photo;

use App\Models\Photo;
use Illuminate\Support\Facades\DB;

class Toggles
{
	public $property;

	public function do(array $photoIDs): bool
	{
		//! DB::raw is safe because WE (dev) have control over $property. It is not influced by user inputs.
		return Photo::whereIn('id', $photoIDs)->update([$this->property => DB::raw('1 XOR `' . $this->property . '`')]);
	}
}
