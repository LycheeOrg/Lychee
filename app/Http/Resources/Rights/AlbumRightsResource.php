<?php

namespace App\Http\Resources\Rights;

use App\Contracts\Models\AbstractAlbum;
use App\Http\Resources\JsonResource;
use App\Policies\AlbumPolicy;
use Illuminate\Support\Facades\Gate;

class AlbumRightsResource extends JsonResource
{
	public function __construct(
		public bool $can_edit,
		public bool $can_share_with_users,
		public bool $can_download,
		public bool $can_upload,
	) {
		parent::__construct();
	}

	/**
	 * Given an album, returns the access rights associated to it.
	 *
	 * @param AbstractAlbum $abstractAlbum
	 *
	 * @return self
	 */
	public static function ofAlbum(AbstractAlbum $abstractAlbum): self
	{
		return new self(
			can_edit: Gate::check(AlbumPolicy::CAN_EDIT, [AbstractAlbum::class, $abstractAlbum]),
			can_share_with_users: Gate::check(AlbumPolicy::CAN_SHARE_WITH_USERS, [AbstractAlbum::class, $abstractAlbum]),
			can_download: Gate::check(AlbumPolicy::CAN_DOWNLOAD, [AbstractAlbum::class, $abstractAlbum]),
			can_upload: Gate::check(AlbumPolicy::CAN_UPLOAD, [AbstractAlbum::class, $abstractAlbum]),
		);
	}

	/**
	 * Transform the resource into an array.
	 *
	 * @param \Illuminate\Http\Request $request
	 *
	 * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
	 */
	public function toArray($request)
	{
		return [
			'can_edit' => $this->can_edit,
			'can_share_with_users' => $this->can_share_with_users,
			'can_download' => $this->can_download,
			'can_upload' => $this->can_upload,
		];
	}
}
