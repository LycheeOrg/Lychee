<?php

namespace App\Actions\Album;

use App\Assets\Helpers;
use App\Models\Logs;
use App\Response;

class SetLicense extends Setter
{
	public function __construct()
	{
		parent::__construct();
		$this->property = 'license';
	}

	public function do(string $albumID, string $value): bool
	{
		$album = $this->albumFactory->make($albumID);

		$licenses = Helpers::get_all_licenses();

		if (!in_array($value, $licenses)) {
			Logs::error(__METHOD__, __LINE__, 'License not recognised: ' . $value);

			return Response::error('License not recognised!');
		}

		return $this->execute($album, $value);
	}
}
