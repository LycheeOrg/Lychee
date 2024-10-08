<?php

namespace App\Http\Resources\Statistics;

use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript()]
class Album extends Data
{
	public string $title;
	public int $left;
	public int $right;
	public int $num_photos;
	public int $num_descendants;
	public int $size;
	public string $formatted;

	/**
	 * @param array{title:string,left:int,right:int,num_photos:int,num_descendants:int,size:int,formatted:string} $album
	 *
	 * @return void
	 */
	public function __construct(array $album)
	{
		$this->title = $album['title'];
		$this->left = $album['left'];
		$this->right = $album['right'];
		$this->num_photos = $album['num_photos'];
		$this->num_descendants = $album['num_descendants'];
		$this->size = $album['size'];
		$this->formatted = $album['formatted'];
	}

	/**
	 * @param array{title:string,left:int,right:int,num_photos:int,num_descendants:int,size:int,formatted:string} $album
	 *
	 * @return Album
	 */
	public static function fromArray(array $album): self
	{
		return new self($album);
	}
}
