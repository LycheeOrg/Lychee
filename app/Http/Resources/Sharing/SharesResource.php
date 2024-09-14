<?php

namespace App\Http\Resources\Sharing;

use Illuminate\Support\Collection;
use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\LiteralTypeScriptType;

/**
 * Data Transfer Object (DTO) to transmit the list of shares to the client.
 */
class SharesResource extends Data
{
	/** @var Collection<int,SharedAlbumResource> */
	#[LiteralTypeScriptType('App.Http.Resources.Sharing.SharedAlbumResource[]')]
	public Collection $shared;
	/** @var Collection<int,ListedAlbumsResource> */
	#[LiteralTypeScriptType('App.Http.Resources.Sharing.ListedAlbumsResource[]')]
	public Collection $albums;
	/** @var Collection<int,UserSharedResource> */
	#[LiteralTypeScriptType('App.Http.Resources.Sharing.UserSharedResource[]')]
	public Collection $users;

	/**
	 * @param Collection<int,object{id:int,user_id:int,album_id:string,username:string,title:string}> $shared
	 * @param Collection<int,object{id:string,title:string}>                                          $albums
	 * @param Collection<int,object{id:int,username:string}>                                          $users
	 *
	 * @return void
	 */
	public function __construct(
		Collection $shared,
		Collection $albums,
		Collection $users)
	{
		$this->shared = SharedAlbumResource::collect($shared);
		$this->albums = ListedAlbumsResource::collect($albums);
		$this->users = UserSharedResource::collect($users);
	}
}
