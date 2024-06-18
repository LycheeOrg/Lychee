<?php

declare(strict_types=1);

namespace App\Livewire\Synth;

use App\Contracts\Models\AbstractAlbum;
use App\Factories\AlbumFactory;
use Livewire\Mechanisms\HandleComponents\Synthesizers\Synth;

class AlbumSynth extends Synth
{
	public static string $key = 'a';

	public static function match(mixed $target): bool
	{
		return $target instanceof AbstractAlbum;
	}

	/**
	 * @param AbstractAlbum $target
	 *
	 * @return array<int,array<string,string>>
	 */
	public function dehydrate($target): array
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