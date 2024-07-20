<?php

namespace App\Http\Resources\Rights;

use App\Contracts\Models\AbstractAlbum;
use App\Policies\AlbumPolicy;
use Illuminate\Support\Facades\Gate;
use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript()]
class RootAlbumRightsResource extends Data
{
	public bool $can_edit;
	public bool $can_upload;
	public bool $can_import_from_server;

	public function __construct()
	{
		$this->can_edit = Gate::check(AlbumPolicy::CAN_UPLOAD, [AbstractAlbum::class, null]);
		$this->can_upload = Gate::check(AlbumPolicy::CAN_UPLOAD, [AbstractAlbum::class, null]);
		$this->can_import_from_server = Gate::check(AlbumPolicy::CAN_IMPORT_FROM_SERVER, [AbstractAlbum::class]);
	}
}
