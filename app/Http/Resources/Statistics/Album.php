<?php

namespace App\Http\Resources\Statistics;

use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript()]
class Album extends Data
{
	public string $username;
	public string $title;
	public bool $is_nsfw;
	public int $left;
	public int $right;
	public int $num_photos;
	public int $num_descendants;
	public int $size;

	/**
	 * @param array{username:string,title:string,is_nsfw:bool,left:int,right:int,num_photos:int,num_descendants:int,size:int} $album
	 *
	 * @return void
	 */
	public function __construct(array $album)
	{
		$this->username = $album['username'];
		$this->title = $album['title'];
		$this->is_nsfw = $album['is_nsfw'];
		$this->left = $album['left'];
		$this->right = $album['right'];
		$this->num_photos = $album['num_photos'];
		$this->num_descendants = $album['num_descendants'];
		$this->size = $album['size'];
	}

	/**
	 * @param array{username:string,title:string,is_nsfw:bool,left:int,right:int,num_photos:int,num_descendants:int,size:int} $album
	 *
	 * @return Album
	 */
	public static function fromArray(array $album): self
	{
		return new self($album);
	}
}
