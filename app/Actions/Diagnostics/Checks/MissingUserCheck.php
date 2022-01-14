<?php

namespace App\Actions\Diagnostics\Checks;

use App\Contracts\DiagnosticCheckInterface;
use App\Metadata\LycheeVersion;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class MissingUserCheck implements DiagnosticCheckInterface
{
	/**
	 * @var LycheeVersion
	 */
	private $lycheeVersion;

	/**
	 * @var array
	 */
	private $versions;

	/**
	 * @param LycheeVersion $lycheeVersion
	 * @param array caching the return of lycheeVersion->get()
	 */
	public function __construct(
		LycheeVersion $lycheeVersion
	) {
		$this->lycheeVersion = $lycheeVersion;

		$this->versions = $this->lycheeVersion->get();
	}

	public function check(array &$errors): void
	{
		if ($this->versions['DB']['version'] >= '4.5.0') {
			$album_owners = DB::table('base_albums')->select('owner_id')->groupBy('owner_id')->pluck('owner_id');
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
}
