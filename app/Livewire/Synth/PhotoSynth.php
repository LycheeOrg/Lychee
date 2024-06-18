<?php

declare(strict_types=1);

namespace App\Livewire\Synth;

use App\Models\Photo;
use Livewire\Mechanisms\HandleComponents\Synthesizers\Synth;

class PhotoSynth extends Synth
{
	public static string $key = 'p';

	public static function match(mixed $target): bool
	{
		return $target instanceof Photo;
	}

	/**
	 * @param Photo $target
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
	 * @return Photo
	 */
	public function hydrate($value): Photo
	{
		return Photo::findOrFail($value['id']);
	}
}