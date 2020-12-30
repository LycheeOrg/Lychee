<?php

namespace App\Contracts;

use App\Actions\Photo\Create;
use App\Models\Photo;

interface AddPhotoStrategyInterface
{
	public function storeFile(Create $create);

	public function hydrate(Create &$create, ?Photo &$existing = null, ?array $file = null);

	public function loadMetadata(Create &$create, array $file);

	public function generate_thumbs(Create &$create, bool &$skip_db_entry_creation, bool &$no_error);
}
