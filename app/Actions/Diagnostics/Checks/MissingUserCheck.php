<?php

namespace App\Actions\Diagnostics\Checks;

use App\Contracts\DiagnosticCheckInterface;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class MissingUserCheck implements DiagnosticCheckInterface
{
	public function check(array &$errors): void
	{
		$album_owners = DB::table('albums')->select('owner_id')->groupBy('owner_id')->pluck('owner_id');
		$photo_owners = DB::table('photos')->select('owner_id')->groupBy('owner_id')->pluck('owner_id');
		$owner_ids = $album_owners->concat($photo_owners)->unique()->values();
		foreach ($owner_ids as $owner_id) {
			$candidate = User::find($owner_id);
			if ($candidate == null) {
				$errors[] = 'Error: A user is missing! Please create a user with id: "' . $owner_id . '"';
			}
		}
	}
}
