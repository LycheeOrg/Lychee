<?php

namespace App\DTO\Synth;

use App\Contracts\Models\AbstractAlbum;
use App\Factories\AlbumFactory;
use Livewire\Mechanisms\HandleComponents\Synthesizers\Synth;

class AlbumSynth extends Synth
{
	public static $key = 'a';

	public static function match($target)
	{
		return $target instanceof AbstractAlbum;
	}

	/**
	 * @param AbstractAlbum $target
	 *
	 * @return string
	 */
	public function dehydrate($target)
	{
		return [[
			'id' => $target->id,
		], []];
	}

	/**
	 * @param array<string,string> $value
	 *
	 * @return AbstractAlbum
	 */
	public function hydrate($value): AbstractAlbum
	{
		/** @var AlbumFactory $albumFactory */
		$albumFactory = resolve(AlbumFactory::class);

		return $albumFactory->findAbstractAlbumOrFail($value['id']);
	}
}